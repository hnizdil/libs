<?php

namespace Hnizdil\Factory;

use PDOException;
use InvalidArgumentException;
use Hnizdil\Service\WwwPathGetter;
use Hnizdil\Factory\EntityFormFactoryException as e;
use Doctrine\ORM\Mapping as ORM;
use Hnizdil\ORM\AbstractEntity;
use Doctrine\DBAL\Types;
use Nette\DI\IContainer;
use Nette\Forms\Controls\SubmitButton;
use Nette\ComponentModel\IContainer as IComponentContainer;
use Nette\Http\FileUpload;
use Nette\ObjectMixin;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Hnizdil\Doctrine\EntityForm;
use Hnizdil\Nette\Forms\EntityContainer;
use Kdyby\Forms\Containers\Replicator;

class EntityFormFactory
{

	protected $em;
	protected $container;
	protected $entityFactory;
	protected $wwwPathGetter;
	protected $translator;
	protected $uploads = array();

	public function __construct(
		IContainer        $container,
		ObjectManager     $em,
		TranslatorFactory $translatorFactory,
		EntityFactory     $entityFactory,
		WwwPathGetter     $wwwPathGetter
	) {

		try {
			$this->translator = $translatorFactory->create();
		}
		catch (InvalidArgumentException $e) {
		}

		$this->em            = $em;
		$this->container     = $container;
		$this->entityFactory = $entityFactory;
		$this->wwwPathGetter = $wwwPathGetter;

	}

	public function create($entity, array $callbacks) {

		$form = new EntityForm;

		$form->setEntity($entity);

		$form->setCallbacks($callbacks);

		$this->populateContainer($form, $entity);

		$form->addSubmit('send', 'Uložit')
			->onClick[] = array($this, 'processData');

		if ($entity instanceof AbstractEntity && !$entity->isFresh()) {
			$form->addSubmit('delete', 'Smazat')
				->onClick[] = array($this, 'doDelete');
		}

		if ($this->translator) {
			$form->setTranslator($this->translator);
		}

		$form->getElementPrototype()->class('entity-form');

		return $form;

	}

	public function populateContainer(IComponentContainer $container, $entity) {

		$em = $this->em;

		$form = $container->getForm();

		if ($entity instanceof AbstractEntity) {
			$editingExistingEntity = TRUE;
		}
		else {
			$editingExistingEntity = FALSE;
			$entity = $this->entityFactory->create($entity);
		}

		$classMeta = $em->getClassMetadata(get_class($entity));

		$fieldsContainer = $container->addContainer('fields');

		foreach ($classMeta->formFields as $field => $attrs) {

			$default = $value = $entity->$field;

			$fieldMeta = @$classMeta->fieldMappings[$field]       ?: array();
			$assocMeta = @$classMeta->associationMappings[$field] ?: array();
			$gridMeta  = @$classMeta->gridFields[$field]          ?: array();
			$formMeta  = @$classMeta->formFields[$field]          ?: array();

			// editovatelná asociace
			if ($assocMeta && $formMeta['editableEntity']) {

				$isToOne = $assocMeta['type'] & ORM\ClassMetadataInfo::TO_ONE;

				$maxCount = $isToOne ? 1 : $formMeta['editableEntityMaxCount'];
				$minCount = min($formMeta['editableEntityMinCount'], $maxCount);

				$subContainerCount = $formMeta['editableEntityInitCount'];
				// zabalení samotné entity do jednoprvkové kolekce
				if ($value instanceof AbstractEntity) {
					$value = new ArrayCollection(array($value));
				}
				if ($value instanceof Collection && !$value->isEmpty()) {
					$subContainerCount = count($value)
						+ $formMeta['editableEntityAdditionalCount'];
				}

				// dynamický kontejner (nepoužívá se addDynamic(),
				// protože při $createDefault nelze nastavit $containerClass)
				$this_ = $this;
				$replicator = new Replicator(function ($container)
					use ($form, $assocMeta, $value, $em, $minCount, $this_) {

						// vytvoření inputů pro atributy entity
						$this_->populateContainer($container,
							@$value[$container->name]
							?: $assocMeta['targetEntity']);

						// tlačítko pro odstranění kontejneru
						$container->addSubmit('remove', 'Odstranit')
							->setValidationScope(FALSE)
							->onClick[] =
							function (SubmitButton $button)
							use ($form, $container, $em, $minCount) {
								$replicator = $container->parent;
								// ověření minimálního počtu kontejnerů
								$count = count($replicator->getContainers());
								if ($minCount && $count <= $minCount) {
									$button->addError('Objekt nelze odebrat');
									return;
								}
								// smazání entity
								$values = $container->getValues();
								$id = json_decode($values['id'], TRUE);
								if ($id) {
									$entity = $em->find($values['class'], $id);
									if ($entity instanceof AbstractEntity) {
										$em->remove($entity);
										$em->flush();
									}
								}
								// odstranění kontejneru
								$replicator->remove($container, TRUE);
								// přesměrování, ať tam nestraší signál
								$form->presenter->redirect('this');
							};

					}, $subContainerCount);

				// použijeme EntityContainer
				$replicator->containerClass =
					'\\Hnizdil\\Nette\\Forms\\EntityContainer';

				// tlačítko pro přidání kontejneru
				$replicator
					->addSubmit('add', 'Přidat')
					->setValidationScope(FALSE)
					->onClick[] = function (SubmitButton $button)
					use ($maxCount) {
						// ověření maximálního počtu kontejnerů
						$count = count($button->parent->getContainers());
						if ($maxCount && $count >= $maxCount) {
							$button->addError('Nelze přidat další objekt');
							return;
						}
						$container = $button->parent->createOne();
					};

				$fieldsContainer->addComponent($replicator, $field);

				continue;
			}

			// asociace, vznikne select nebo multiselect
			elseif ($assocMeta) {
				$entities = $this->getAssociableEntities(
					$assocMeta['targetEntity']);
				$targetClassMeta = $em->getClassMetadata(
					$assocMeta['targetEntity']);
				$items = array();
				foreach ($entities as $e) {
					// pro formulářové prvky se použijí jiná jména entit
					if ($formMeta['targetNameCols']) {
						$entityNameParts = array();
						foreach ($formMeta['targetNameCols'] as $column) {
							$entityNameParts[] = $e->$column;
						}
						$entityName = implode(
							$formMeta['targetNameColsSeparator'], $entityNameParts);
					}
					// použijí se běžná jména entit
					else {
						$entityName = $targetClassMeta->getEntityName($e);
					}
					$entityId = $targetClassMeta->getIdentifierValues($e);
					$items[json_encode($entityId)] = $entityName;
				}
				if ($formMeta['itemsSorted']) {
					asort($items);
				}
				// *ToOne
				if ($assocMeta['type'] & ORM\ClassMetadataInfo::TO_ONE) {
					$control = $fieldsContainer->addSelect($field, NULL, $items);
					if ($value instanceof AbstractEntity) {
						$default = json_encode(
							$classMeta->getIdentifierValues($value));
					}
					if (@$assocMeta['joinColumns'][0]['nullable']) {
						$control->setPrompt($formMeta['optionalControlPrompt']);
					}
					else {
						$control->setPrompt($formMeta['controlPrompt']);
					}
				}
				// *ToMany
				else {
					$control = $fieldsContainer->addMultiSelect($field, NULL, $items);
					$default = array();
					if ($value instanceof Collection) {
						foreach ($value as $associatedEntity) {
							$default[] = json_encode(
								$classMeta->getIdentifierValues($associatedEntity));
						}
					}
				}
				if ($formMeta['required']
					|| (
						isset($assocMeta['joinColumns'][0]['nullable'])
						&& !$assocMeta['joinColumns'][0]['nullable']
					)
					|| (
						isset($assocMeta['joinTable']['inverseJoinColumns'][0]['nullable'])
						&& !$assocMeta['joinTable']['inverseJoinColumns'][0]['nullable']
					)
				) {
					$control->setRequired();
				}
			}

			// Běžné políčko
			else {
				$ruleFilled = $formMeta['required'] === NULL
					? !$fieldMeta['nullable']
					: $formMeta['required'];
				$anotherRules = array();
				$type = Types\Type::getType($fieldMeta['type']);

				if ($type instanceof \Hnizdil\DBAL\IntToStringType) {
					if ($formMeta['control'] == 'RadioList') {
						$items = $type->getSelectItems();
						$control = $fieldsContainer->addRadioList($field, NULL, $items);
						$control->getSeparatorPrototype()
							->setName('span class=radio-separator');
						$itemKeys = array_keys($items);
						$firstItemKey = array_shift($itemKeys);
						$control->setValue($firstItemKey);
					}
					else {
						$control = $fieldsContainer->addSelect(
							$field, NULL, $type->getSelectItems());
					}
				}
				elseif ($type instanceof \Hnizdil\DBAL\EnumType
					||  $type instanceof \Hnizdil\DBAL\MapType
				) {
					$items = $type->getSelectItems();
					$fieldMeta['length'] = NULL;
					if ($formMeta['control'] == 'Hidden') {
						$control = $fieldsContainer->addHidden($field);
					}
					elseif ($formMeta['control'] == 'RadioList') {
						if (!$ruleFilled) {
							$items = array('' => $formMeta['controlPrompt']) + $items;
						}
						$control = $fieldsContainer
							->addRadioList($field, NULL, $items);
						$control->getSeparatorPrototype()
							->setName('span class=radio-separator');
						if (!$ruleFilled) {
							$control->setValue($formMeta['controlPrompt']);
						}
					}
					else {
						$control = $fieldsContainer->addSelect($field, NULL, $items);
						if ($ruleFilled) {
						}
						else {
							$control->setPrompt($formMeta['controlPrompt']);
						}
					}
				}
				elseif ($type instanceof \Hnizdil\DBAL\MoneyType) {
					$control = $fieldsContainer->addMoney(
						$field, NULL, $formMeta['currency']);
					$control->getControlPrototype()->class('money');
				}
				elseif ($type instanceof \Hnizdil\DBAL\UrlType) {
					$control = $fieldsContainer->addText($field);
					$anotherRules[] = array($form::URL);
				}
				elseif ($type instanceof \Hnizdil\DBAL\EmailType) {
					$control = $fieldsContainer->addText($field);
					$anotherRules[] = array($form::EMAIL);
				}
				elseif ($type instanceof Types\DateType) {
					$control = $fieldsContainer->addDate($field);
				}
				elseif ($type instanceof Types\TimeType) {
					$control = $fieldsContainer->addTime($field);
				}
				elseif ($type instanceof Types\DateTimeType) {
					$control = $fieldsContainer->addDateTime($field);
				}
				elseif ($type instanceof Types\IntegerType
					|| $type instanceof Types\SmallIntType
					|| $type instanceof Types\BigIntType) {
					$anotherRules[] = array($form::INTEGER);
					$control = $fieldsContainer->addText($field);
				}
				elseif ($type instanceof Types\FloatType) {
					$anotherRules[] = array($form::FLOAT);
					$control = $fieldsContainer->addText($field);
				}
				elseif ($type instanceof Types\TextType) {
					$control = $fieldsContainer->addTextarea($field);
					if ($formMeta['wysiwyg']) {
						$control->getControlPrototype()->class('wysiwyg');
					}
				}
				elseif ($type instanceof Types\BooleanType) {
					if ($fieldMeta['nullable']) {
						$control = $fieldsContainer->addRadioList($field, NULL, array(
							2 => Html::el('em')->setText('(nic)'),
							1 => 'ano',
							0 => 'ne',
						));
						$control->getSeparatorPrototype()
							->setName('span')->class('sep');
						$ruleFilled = TRUE;
						$default = is_bool($default) ? (int)$default : 2;
					}
					else {
						$control = $fieldsContainer->addCheckbox($field);
						$ruleFilled = FALSE;
					}
				}
				elseif ($type instanceof Types\ArrayType) {
					$items = array_combine(
						$formMeta['allowedValues'],
						$formMeta['allowedValues']);
					$control = $fieldsContainer->addCheckboxList(
						$field, NULL, $items);
					$control->getSeparatorPrototype()->setName(NULL);
					$ruleFilled = FALSE;
				}
				elseif ($type instanceof \Hnizdil\DBAL\PasswordType) {
					$control = $fieldsContainer->addPassword($field);
					$ruleFilled = $ruleFilled && !$editingExistingEntity;
				}
				elseif ($type instanceof \Hnizdil\DBAL\FileType) {
					$control = $fieldsContainer->addUpload($field);
					$control->setOption('filename', $value);
					if ($value && $formMeta['uploadDirParam']) {
						$uploadDir = $this->container->expand(
							"%{$formMeta['uploadDirParam']}%");
						$filePath = "{$uploadDir}/{$value}";
						$wwwPath  = $this->wwwPathGetter->get($filePath);
						$control->setOption('filePath', $filePath);
						$control->setOption('wwwPath', $wwwPath);
					}
					$ruleFilled = FALSE;
					$fieldMeta['length'] = NULL;
				}
				else {
					$control = $fieldsContainer->addText($field);
				}

				if ($fieldMeta['length']) {
					$anotherRules[] = array(
						$form::MAX_LENGTH, NULL, $fieldMeta['length']);
				}

				if ($ruleFilled) {
					$rulesTarget = $control->addRule($form::FILLED);
					$control->getLabelPrototype()->class[] = 'required';
				}
				elseif ($anotherRules) {
					$rulesTarget = $control->addCondition($form::FILLED);
				}

				foreach ($anotherRules as $rule) {
					call_user_func_array(array($rulesTarget, 'addRule'), $rule);
				}
			}

			$control->setValue($default);

			$control->caption = @$formMeta['title']
				?: (@$gridMeta['title'] ?: $field);

			if ($formMeta['description']) {
				$control->setOption('description', $formMeta['description']);
			}

		}

		$container->addHidden('class')->setValue($classMeta->name);

		$idValues = $classMeta->getIdentifierValues($entity);
		foreach ($idValues as &$idValue) {
			if ($idValue instanceof AbstractEntity) {
				$em->refresh($idValue); // vynucení naplnění proxy z databáze
				$subIdentifierValues = $em
					->getClassMetadata(get_class($idValue))
					->getIdentifierValues($idValue);
				$idValue = array_pop($subIdentifierValues);
			}
		}
		$container->addHidden('id')->setValue(json_encode($idValues));

		if ($container instanceof EntityContainer) {
			$container->setEntity($entity);
		}

		$form->postContainer($container, $entity);

	}

	public function processData(SubmitButton $button) {

		$em     = $this->em;
		$form   = $button->form;
		$entity = $this->processEntityValues($form);

		$form->prePersist($entity, $form);

		$em->persist($entity);

		$form->preFlush($entity, $form);

		if ($form->hasErrors()) {
			return FALSE;
		}

		try {
			$em->flush();
		}
		catch (PDOException $e) {
			if ($e->errorInfo[1] == 1062) {
				$pattern = "~Duplicate entry '(.*?)' for key '(.*?)'~";
				preg_match($pattern, $e->getMessage(), $matches);
				$meta = $em->getClassMetadata(get_class($form->getEntity()));
				$formMeta = @$meta->formFields[$meta->fieldNames[$matches[2]]];
				if ($formMeta) {
					$error = sprintf(
						'Objekt mající položku „%s“ rovnou „%s“ už existuje.',
						$formMeta['title'],
						$matches[1]);
				}
				else {
					$error = sprintf(
						'Hodnota „%s“ je již použita u jiného objektu.',
						$matches[1]);
				}
				$form->addError($error);
				return FALSE;
			}
			else {
				throw $e;
			}
		}

		if ($this->uploads) {
			$this->moveUploads();
		}

		$form->postFlush($entity, $form);

		// přesměrujeme pouze pokud byl formulář uložen defaultním tlačítkem
		if ($button === $form['send']) {
			$form->presenter->redirect('this');
		}

	}

	public function doDelete(SubmitButton $button) {

		$em     = $this->em;
		$form   = $button->form;
		$entity = $form->getEntity();

		if ($entity instanceof AbstractEntity) {
			$em->remove($entity);
			$em->flush();
		}

		$form->postDelete($entity, $form);

		$form->presenter->redirect('this');

	}

	private function processEntityValues(IComponentContainer $container) {

		$values = $container->getValues(TRUE);

		$em = $this->em;

		$entity = ($id = json_decode($values['id'], TRUE))
			? $em->find($values['class'], $id)
			: $this->entityFactory->create($values['class']);

		$classMeta = $em->getClassMetadata(get_class($entity));

		foreach ($values['fields'] as $field => $value) {

			// případné zpracování uměle vytvořených políček
			if (!isset($classMeta->fieldMappings[$field]) &&
				!isset($classMeta->associationMappings[$field])
			) {
				$container->form->virtualField(
					$entity, $field, $value, $container['fields'][$field]);
				continue;
			}

			$inputMeta = @$classMeta->formFields[$field]          ?: array();
			$fieldMeta = @$classMeta->fieldMappings[$field]       ?: array();
			$assocMeta = @$classMeta->associationMappings[$field] ?: array();

			// asociace
			if ($assocMeta) {

				$isToOne = $assocMeta['type'] & ORM\ClassMetadataInfo::TO_ONE;

				// data dalších entit
				if ($inputMeta['editableEntity']) {
					foreach ($value as $subContainerName => $_) {
						$subEntity = $this->processEntityValues(
							$container['fields'][$field][$subContainerName]);
						$this->setAssoc($entity, $field, $subEntity);
					}
				}

				// toOne asociace
				elseif ($isToOne) {
					// nastavení asociace
					if ($value) {
						$key = json_decode($value, TRUE);
						$target = $assocMeta['targetEntity'];
						$assocEntity = $em->find($target, $key);
						if (!$assocEntity) {
							e::associatedEntityNotFound($target, $key);
						}
						$this->setAssoc($entity, $field, $assocEntity);
					}
					// zrušení asociace
					elseif ($entity->$field) {
						$this->unsetAssoc($entity, $field);
					}
				}

				// toMany asociace
				else {
					// pomocná kolekce
					$selected = new ArrayCollection();
					// přidání naklikaných entit
					foreach ($value as $identifier) {
						$selected[] = $em->find(
							$assocMeta['targetEntity'],
							json_decode($identifier, TRUE));
					}
					// odebrání aktuálně připojených, ale nenaklikaných entit
					foreach ($entity->$field as $assocEntity) {
						if (!$selected->contains($assocEntity)) {
							$this->unsetAssoc($entity, $field, $assocEntity);
						}
					}
					// přidání naklikaných, ale aktuálně nepřipojených entit
					foreach ($selected as $selectedEntity) {
						if (!$entity->$field->contains($selectedEntity)) {
							$this->setAssoc($entity, $field, $selectedEntity);
						}
					}
				}

				continue;

			}

			// boolean
			if ($fieldMeta['type'] == 'boolean') {
				switch ($value) {
				case 0:  $value = FALSE; break;
				case 1:  $value = TRUE;  break;
				default: $value = NULL;  break;
				}
			}

			if ($value instanceof FileUpload) {
				// soubor nahrajeme
				if ($value->isOk()) {
					if ($inputMeta['uploadDirParam']) {
						$this->uploads[] = array(
							$inputMeta,
							$field,
							$entity,
							clone $value,
						);
						// jméno souboru bude nastaveno v $this->moveUploads()
						$value = $entity->$field ? $entity->$field : 'dummy';
					}
					else {
						// o zpracování souboru se postará programátor
					}
				}
				// soubor ignorujeme
				else {
					continue;
				}
			}

			// běžná hodnota, nastavíme ji entitě
			ObjectMixin::set($entity, $field, $value);

		}

		return $entity;

	}

	private function setAssoc($entity, $field, $otherEntity = NULL) {

		list($inputMeta, $assocMeta) =
			$this->preAssoc($entity, $field, $otherEntity);

		$setter = @$inputMeta['setter'] ?: ('set' . $field);

		if (method_exists($entity, $setter)) {
			$entity->$setter($otherEntity);
		}
		else {
			if ($assocMeta['type'] & ORM\ClassMetadataInfo::TO_ONE) {
				ObjectMixin::set($entity, $field, $otherEntity);
			}
			else {
				$entityClassName = $this->em
					->getClassMetadata(get_class($entity))->name;
				e::noToManySetter($entityClassName, $field);
			}
		}

	}

	private function unsetAssoc($entity, $field, $otherEntity = NULL) {

		list($inputMeta, $assocMeta) =
			$this->preAssoc($entity, $field, $otherEntity);

		$unsetter = @$inputMeta['unsetter'];

		if (method_exists($entity, $unsetter)) {
			$entity->$unsetter($otherEntity);
		}
		else {
			if ($assocMeta['type'] & ORM\ClassMetadataInfo::TO_ONE) {
				ObjectMixin::set($entity, $field, NULL);
			}
			else {
				$entityClassName = $this->em
					->getClassMetadata(get_class($entity))->name;
				e::noToManyUnsetter($entityClassName, $field);
			}
		}

	}

	private function preAssoc($entity, $field, $otherEntity = NULL) {

		$meta       = $this->em->getClassMetadata(get_class($entity));
		$assocMeta  = @$meta->associationMappings[$field] ?: array();
		$inputMeta  = @$meta->formFields[$field]          ?: array();

		// jedná se o asociaci?
		if (!$assocMeta) {
			e::noAssociation($meta->name, $field);
		}

		// mají mezi sebou entity vztah?
		if ($otherEntity) {
			$otherMeta = $this->em->getClassMetadata(get_class($otherEntity));
			if ($otherMeta->name != $assocMeta['targetEntity']) {
				e::entitiesNotAssociated(
					$otherMeta->name, $assocMeta['targetEntity']);
			}
		}

		return array($inputMeta, $assocMeta);

	}

	protected function getAssociableEntities($entityClassName) {

		return $this->em->getRepository($entityClassName)->findAll();

	}

	protected function moveUploads() {

		foreach ($this->uploads as $up) {

			list($meta, $field, $entity, $upload) = $up;

			// destination directory
			$uploadDir =
				$this->container->expand("%{$meta['uploadDirParam']}%");

			// remove previously uploaded file
			$previousFilePath = $uploadDir . '/' . $entity->$field;
			if (is_file($previousFilePath)) {
				@unlink($previousFilePath);
			}

			// field to get the name of destination subdirectory
			$pathField = $entity;
			$path = explode('.', $meta['uploadDirNamePath']);
			while ($part = array_shift($path)) {
				$pathField = $pathField->$part;
			}

			// path to destination filename
			$path = $uploadDir
				. '/' . Strings::webalize($pathField)
				. '/' . $upload->getSanitizedName();

			// move uploaded file
			$upload->move($path);

			// set file path relative to destination directory
			ObjectMixin::set($entity, $field,
				mb_substr($path, mb_strlen($uploadDir) + 1));

		}

		$this->em->flush();

	}

}
