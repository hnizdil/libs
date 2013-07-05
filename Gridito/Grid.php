<?php

namespace Hnizdil\Gridito;

use DateTime;
use InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Collections\Collection;
use Gridito\Column;
use Hnizdil\Factory\TranslatorFactory;
use Hnizdil\Nette\Forms\Controls\DateInput;
use Hnizdil\ORM\AbstractEntity;
use Nette\ObjectMixin;
use Nette\Application\UI\Form;
use Nette\DI\IContainer;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

class Grid
	extends \Gridito\Grid
{

	/**
	 * @var integer
	 * @persistent
	 */
	public $itemsPerPage = 10;

	/**
	 * @var array
	 * @persistent
	 */
	public $filter = array();

	public $isSortable = FALSE;

	private $model;

	private $container;

	private $classMeta;

	private $translator;

	private $em;

	private $beforeRemove;

	private $keyParam = 'id';

	private $multiActions = array();

	private $defaultFilter = array();

	private $filterContainerCallback;

	public function __construct(
		IContainer                $container,
		EntityManager             $em,
		DoctrineQueryBuilderModel $model,
		TranslatorFactory         $translatorFactory,
		                          $entityClassName
	) {

		parent::__construct();

		$this->em        = $em;
		$this->container = $container;
		$this->classMeta = $em->getClassMetadata($entityClassName);
		$this->model     = $model;

		$this->setHighlightOrderedColumn(FALSE);

		$this->translator = $translatorFactory->create();
		$this->template->setTranslator($this->translator);

		foreach ($this->classMeta->gridFields as $field => $fieldMeta) {
			if ($fieldMeta['autoAdd']) {
				$this->addColumn($field);
			}
		}

		if ($this->classMeta->hasGridActionEdit()) {
			$grid = $this;
			$classMeta = $this->classMeta;
			$this->addButton('edit', $this->translator->translate('Editovat'), array(
				'link' => function (AbstractEntity $entity) use ($grid, $classMeta) {
					return $grid->presenter->link('detail', array(
						$grid->getKeyParam() =>
							$classMeta->getIdentifierValues($entity),
					));
				},
			));
		}

		$this->getTemplate()->setFile(__DIR__ . '/gridTemplate.phtml');

		$this->setRowClass(function ($iterator, $row) {
			return $iterator->isOdd() ? 'odd' : NULL;
		});

	}

	public function addColumn($name, $label = null, array $options = array()) {

		$fieldMeta = @$this->classMeta->fieldMappings[$name];
		$gridMeta  = @$this->classMeta->gridFields[$name];

		$options['renderer'] = array($this, 'renderFunction');

		if ($gridMeta) {
			$options['sortable'] = $gridMeta['isSortable'];
		}

		// podle *ToMany nelze řadit
		$assocMeta = @$this->classMeta->associationMappings[$name];
		if ($assocMeta && ($assocMeta['type'] & ClassMetadataInfo::TO_MANY)) {
			$options['sortable'] = FALSE;
		}

		// přidání sloupce
		$column = parent::addColumn(
			$name, @$gridMeta['title'] ?: $label, $options);

		// sežazeno podle sloupce
		if ($gridMeta && $gridMeta['defaultSortType'] != '') {
			$this->setDefaultSorting($name,
				$gridMeta['defaultSortType'] == 'asc' ? 'asc' : 'desc');
		}

		$column->setCellClass(function () use ($name, $fieldMeta, $gridMeta) {
			return trim("{$name} type-{$fieldMeta['type']} {$gridMeta['cellCssClassAppend']}");
		});

		// je možné měnit pořadí položek přes drag'n'drop
		if ($gridMeta['isSortingHandle']) {
			$this->isSortable = TRUE;
		}

		return $column;

	}

	public function handleApplySorting($from, $prev, $next) {

		$em   = $this->em;
		$col  = $this->sortColumn;
		$meta = $this->classMeta;

		// položky nejsou seřazeny podle pořadí
		if (!@$meta->gridFields[$col]['isSortingHandle']) {
			return;
		}

		// přesouvaná entita
		$from = $em->find($meta->name, json_decode($from, TRUE));

		// entita nenalezena
		if (!$from) {
			return;
		}

		// entita stojící před novým umístěním
		if ($prev) {
			$prev = $em->find($meta->name, json_decode($prev, TRUE));
		}

		// entita stojící za novým umístěním
		if ($next) {
			$next = $em->find($meta->name, json_decode($next, TRUE));
		}

		// alespoň $prev nebo $next musí existovat
		if (!$prev && !$next) {
			return;
		}
		// $prev neexistuje, použijeme $next
		elseif (!$prev) {
			$dest = $next;
		}
		// $next neexistuje, použijeme $prev
		elseif (!$next) {
			$dest = $prev;
		}
		// vybereme tu bližší
		else {
			$prevDist = abs($prev->$col - $from->$col);
			$nextDist = abs($next->$col - $from->$col);
			$dest = $prevDist < $nextDist ? $prev : $next;
		}

		if ($from->$col < $dest->$col) {
			$min    = $from->$col;
			$max    = $dest->$col;
			$change = -1;
		}
		else {
			$min    = $dest->$col;
			$max    = $from->$col;
			$change = +1;
		}

		// zrušíme pozici u přesouvané entity
		ObjectMixin::set($from, $col, NULL);
		$em->flush($from);

		// změníme pozici u všech entit mezi starou a novou pozicí
		$em->createQuery("
			UPDATE {$meta->name} e
			SET e.{$col} = e.{$col} + :change
			WHERE e.{$col} BETWEEN :min AND :max
			")->setParameters(array(
				'change' => $change,
				'min'    => $min,
				'max'    => $max,
			))->execute();

		// nastavíme novou pozici u přesouvané entity
		ObjectMixin::set($from, $col, $dest->$col);
		$em->flush($from);

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}

		$this->presenter->terminate();

	}

	public function renderFunction(AbstractEntity $entity, Column $column) {

		$cm        = $this->classMeta;
		$fieldMeta = @$cm->fieldMappings[$column->columnName]       ?: array();
		$assocMeta = @$cm->associationMappings[$column->columnName] ?: array();
		$gridMeta  = @$cm->gridFields[$column->columnName]          ?: array();

		// sloupec má alias
		if ($this->model->hasColumnAlias($column->columnName)) {
			$value = $this->model->getItemValue($entity, $column->columnName);
		}
		else {
			$value = $entity->{$column->columnName};
		}

		// řadicí sloupec
		if ($gridMeta && $gridMeta['isSortingHandle']) {
			if ($this->sortColumn == $column->columnName) {
				$key = json_encode($cm->getIdentifierValues($entity));
				$html = Html::el('span')
					->class('handle')
					->setText('↕')
					->{'data-key'}($key);
			}
			else {
				$html = Html::el('abbr')
					->setText('?')
					->title($this->translator->translate(
						'Aby se dalo měnit pořadí položek, ' .
						'musí být seřazeny podle tohoto sloupce.'));
			}
			echo $html;
			return;
		}
		// více položek
		elseif ($value instanceof Collection) {
			$names = array();
			$assocClassMeta = $this->em->getClassMetadata($assocMeta['targetEntity']);
			foreach ($value as $assocEntity) {
				$names[] = $assocClassMeta->getEntityName($assocEntity);
			}
			echo implode(', ', $names);
			return;
		}
		// datum
		elseif ($value instanceof DateTime) {
			$value = $value->format($gridMeta
				? $gridMeta['format'][$fieldMeta['type']]
				: $column->getDateTimeFormat());
		}
		// boolean
		elseif ($fieldMeta && $fieldMeta['type'] == 'boolean') {
			echo $this->classMeta->gridFields[$column->columnName]
				[$value ? 'boolTrueValue' : 'boolFalseValue'];
			return;
		}
		// asociace
		elseif ($value instanceof AbstractEntity) {
			$nameMethod = @$gridMeta['useShortName']
				? 'getEntityShortName' : 'getEntityName';
			$value = $this->em
				->getClassMetadata(get_class($value))
				->$nameMethod($value);
		}

		$display = $value;

		if (in_array($column->columnName, $this->classMeta->detailLinkFields)) {
			$link = $this->presenter->link('detail', array(
				$this->keyParam =>
					$this->classMeta->getIdentifierValues($entity),
			));
			$display = Html::el('a')->href($link)->setText($display);
		}

		echo $display;

	}

	public function handleSetItemsPerPage($itemsPerPage) {

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}

	}

	public function render() {

		if ($this->isSortable) {
			$this->presenter->addJQueryUiScript();
		}

		if ($this->isSortable || $this->hasCheckboxes()) {
			$this->presenter->addScript('grid.js');
		}

		// formulář je nutné vytvořit už tady kvůli filtrům a počtu položek
		$this->template->form = $this['gridForm'];

		$this->template->itemsPerPage = $this->itemsPerPage;

		$this->setModel($this->model);
		$this->setItemsPerPage($this->itemsPerPage);

		parent::render();

	}

	public function getMultiActionNames() {

		return array_merge(
			$this->classMeta->gridMultiActions,
			array_keys($this->multiActions)
		);

	}

	public function hasCheckboxes() {

		return (bool) $this->getMultiActionNames();

	}

	public function createComponentGridForm() {

		$form = new Form;

		// filtr (nutný před checkboxy)
		if ($this->filterContainerCallback) {

			$filterContainer = $form->addContainer('filter');

			$filterContainer->addSubmit('submit', 'Hledat')->onClick[] =
				function(SubmitButton $button) {
					$values = $button->form->getValues(TRUE);
					$grid = $button->form->parent;
					$grid->filter = $values['filter']['fields'];
					$grid->presenter->redirect('this');
				};

			$filterContainer->addSubmit('reset', 'Vymazat')->onClick[] =
				function(SubmitButton $button) {
					$grid = $button->form->parent;
					$grid->filter = array();
					$grid->presenter->redirect('this');
				};

			$filterContainer['submit']->getControlPrototype()->class('find');
			$filterContainer['reset']->getControlPrototype()->class('clear');

			$fieldsContainer = $filterContainer->addContainer('fields');

			callback($this->filterContainerCallback)
				->invoke($fieldsContainer, $this->model->getQueryBuilder());

			// selectboxy jsou automaticky nepovinné
			$selectBoxes = $fieldsContainer->getComponents(
				TRUE, 'Nette\\Forms\\Controls\\SelectBox');
			foreach ($selectBoxes as $selectbox) {
				$selectbox->setPrompt('');
			}

			// defaultní filtrování
			if ($this->defaultFilter && !$this->filter) {
				$this->filter = $this->defaultFilter;
			}

			if ($this->filter) {
				$fieldsContainer->setDefaults($this->filter);
				$this->addFilterConditions($fieldsContainer->getControls());
			}

		}

		// checkboxy
		if ($this->hasCheckboxes()) {
			$filterContainer = $form->addContainer('selected');
			foreach ($this->model->getItems() as $item) {
				$filterContainer->addCheckbox($this->encodeEntityKey($item));
			}
		}

		// multismazání označených položek
		if ($this->classMeta->hasGridMultiActionDelete()) {
			$label = $this->translator->translate('Smazat označené');
			$form->addSubmit('multi_delete', $label)
				->onClick[] = array($this, 'multiDelete');
		}

		// ručně přidané multiakce
		foreach ($this->multiActions as $name => $params) {
			$label = $this->translator->translate($params['label']);
			$form->addSubmit("multi_{$name}", $label)
				->onClick[] = array($this, 'multiAction');
		}

		return $form;

	}

	public function multiDelete(SubmitButton $button) {

		foreach ($this->getMultiCheckedEntities() as $entity) {
			if (is_callable($this->beforeRemove)) {
				call_user_func($this->beforeRemove, $entity);
			}
			$this->em->remove($entity);
		}

		$this->em->flush();

		$this->redirect('this');

	}

	public function multiAction(SubmitButton $button) {

		preg_match('~^multi_(.*)$~', $button->getName(), $matches);

		$actionParams = @$this->multiActions[$matches[1]];

		if (is_callable($actionParams['callback'])) {
			$entities = $this->getMultiCheckedEntities();
			callback($actionParams['callback'])->invoke($entities);
		}

		$this->redirect('this');

	}

	public function setBeforeRemove($callback) {

		$this->beforeRemove = $callback;

	}

	public function setDefaultFilter(array $defaultFilter) {

		$this->defaultFilter = $defaultFilter;

	}

	public function getKeyParam() {

		return $this->keyParam;

	}

	public function setKeyParam($keyParam) {

		$this->keyParam = $keyParam;

	}

	public function addMultiAction($name, $label, $callback) {

		$this->multiActions[$name] = array(
			'label'    => $label,
			'callback' => $callback,
		);

	}

	public function setFilterContainerCallback($filterContainerCallback) {

		$this->filterContainerCallback = $filterContainerCallback;

	}

	public function getCheckbox(AbstractEntity $item) {

		return $this['gridForm']['selected'][$this->encodeEntityKey($item)];

	}

	public function getModel() {

		return $this->model;

	}

	private function getMultiCheckedEntities() {

		$entities = array();
		$values   = $this['gridForm']->getValues();

		foreach ($values['selected'] as $hexId => $isChecked) {
			if ($isChecked) {
				$key = json_decode(pack('H*', $hexId), TRUE);
				$entities[] = $this->em->find($this->classMeta->name, $key);
			}
		}

		return $entities;

	}

	private function addFilterConditions($controls) {

		$conditions = array();

		$qb = $this->model->getQueryBuilder();

		foreach ($controls as $name => $control) {

			if ($control instanceof SubmitButton) {
				continue;
			}

			$value = $control->getValue();

			if ($value === NULL || $value === '') {
				continue;
			}

			$name      = $control->getOption('ffField', $name);
			$fieldName = $control->parent->getName() . '.' . $name;
			$paramName = 'param' . md5($control->getHtmlId());
			$fields    = $control->getOption('ffAnotherFields', array());
			$fieldType = $control->getOption('ffFieldType', 'string');
			$operator  = $control->getOption('ffOperator', FALSE);
			$fields[]  = $fieldName;
			$orX       = array();

			if ($control instanceof TextInput) {
				foreach ($fields as $fieldName) {
					$orX[] = $qb->expr()->like($fieldName, ':' . $paramName);
				}
				$qb->setParameter($paramName, "%{$value}%");
			}

			elseif ($control instanceof DateInput) {
				if ($operator == 'lte' || $operator == 'gte') {
					foreach ($fields as $fieldName) {
						$orX[] = $qb->expr()
							->$operator($fieldName, ':' . $paramName);
					}
					$qb->setParameter($paramName,
						$value->format('Y-m-d '
							. ($operator == 'lte' ? '23:59:59' : '00:00:00')));
				}
				elseif ($fieldType == 'datetime') {
					foreach ($fields as $fieldName) {
						$orX[] = $qb->expr()->between($fieldName,
							":{$paramName}start", ":{$paramName}end");
					}
					$qb->setParameter(":{$paramName}start",
						$value->format('Y-m-d 00:00:00'));
					$qb->setParameter(":{$paramName}end",
						$value->format('Y-m-d 23:59:59'));
				}
				else {
					foreach ($fields as $fieldName) {
						$orX[] = $qb->expr()->eq($fieldName, ':' . $paramName);
					}
					$qb->setParameter($paramName,
						$value->format('Y-m-d'));
				}
			}

			else {
				foreach ($fields as $fieldName) {
					$orX[] = $qb->expr()->eq($fieldName, ':' . $paramName);
				}
				$qb->setParameter($paramName, $value);
			}

			$conditions[] = call_user_func_array(
				array($qb->expr(), 'orX'), $orX);

		}

		if ($conditions) {
			$qb->andWhere(call_user_func_array(
				array($qb->expr(), 'andX'), $conditions));
		}

	}

	private function encodeEntityKey(AbstractEntity $entity) {

		$meta = $this->em->getClassMetadata(get_class($entity));

		return bin2hex(json_encode($meta->getIdentifierValues($entity)));

	}

}
