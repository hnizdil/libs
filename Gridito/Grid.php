<?php

namespace Hnizdil\Gridito;

use Hnizdil\ORM\AbstractEntity;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Hnizdil\Gridito\FilterForm;
use Hnizdil\Factory\TranslatorFactory;
use Hnizdil\Nette\Localization\NoTranslator;
use Nette\Utils\Html;
use Nette\DI\IContainer;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Doctrine\Common\Persistence\ObjectManager;
use Gridito\Model\AbstractModel;
use Gridito\Column;

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

	private $model;

	private $container;

	private $classMeta;

	private $translator;

	private $em;

	private $beforeRemove;

	private $keyParam = 'id';

	private $multiActions = array();

	/**
	 * udržuje reference na vygenerované checkboxy
	 *
	 * @var array<Nette\Forms\Controls\Checkbox>
	 * @access private
	 */
	private $checkboxes = array();

	public function __construct(
		IContainer $container,
		ObjectManager $em,
		AbstractModel $model,
		TranslatorFactory $translatorFactory,
		NoTranslator $noTranslator,
		$entityClassName
	) {

		parent::__construct();

		$this->em        = $em;
		$this->container = $container;
		$this->classMeta = $em->getClassMetadata($entityClassName);
		$this->model     = $model;

		$this->setHighlightOrderedColumn(FALSE);

		try {
			$translator = $translatorFactory->create();
		}
		catch (\InvalidArgumentException $e) {
			$translator = $noTranslator;
		}

		$this->template->setTranslator($translator);
		$this->translator = $translator;

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

		$fieldMeta = @$this->classMeta->gridFields[$name];

		$options['renderer'] = array($this, 'renderFunction');

		if ($fieldMeta) {
			$options['sortable']  = $fieldMeta['isSortable'];
			$options['cellClass'] = $fieldMeta['cellClass'];
		}

		// podle *ToMany nelze řadit
		$assocMeta = @$this->classMeta->associationMappings[$name];
		if ($assocMeta && ($assocMeta['type'] & ClassMetadataInfo::TO_MANY)) {
			$options['sortable'] = FALSE;
		}

		// přidání sloupce
		$column = parent::addColumn(
			$name, @$fieldMeta['title'] ?: $label, $options);

		// sežazeno podle sloupce
		if ($fieldMeta && $fieldMeta['defaultSortType'] != '') {
			$this->setDefaultSorting($name,
				$fieldMeta['defaultSortType'] == 'asc' ? 'asc' : 'desc');
		}

		$column->setCellClass(function () use ($name) {
			return $name;
		});

		return $column;

	}

	public function attached($presenter) {

		parent::attached($presenter);

		$filterForm = $presenter->getComponent('filterForm', FALSE);

		if ($filterForm instanceof FilterForm) {
			$filterForm->getElementPrototype()->class('filter-form');
			$this->template->filterForm = $filterForm;
			if ($this->filter) {
				$filterForm->setDefaults($this->filter);
				$filterForm->apply($this->model->getQueryBuilder());
			}
		}

		$this->setModel($this->model);

	}

	public function renderFunction(AbstractEntity $entity, Column $column) {

		$fieldMeta = @$this->classMeta->fieldMappings[$column->columnName]       ?: array();
		$assocMeta = @$this->classMeta->associationMappings[$column->columnName] ?: array();
		$gridMeta  = @$this->classMeta->gridFields[$column->columnName]          ?: array();

		// sloupec má alias
		if ($this->model->hasColumnAlias($column->columnName)) {
			$value = $this->model->getItemValue($entity, $column->columnName);
		}
		else {
			$value = $entity->{$column->columnName};
		}

		// více položek
		if ($value instanceof \Doctrine\ORM\PersistentCollection) {
			$names = array();
			$assocClassMeta = $this->em->getClassMetadata($assocMeta['targetEntity']);
			foreach ($value as $assocEntity) {
				$names[] = $assocClassMeta->getEntityName($assocEntity);
			}
			echo implode(', ', $names);
			return;
		}
		// datum
		elseif ($value instanceof \DateTime) {
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

	// TODO: tohoto se zbavit
	public function render($title = '') {

		if ($this->hasCheckboxes()) {
			$this->presenter->addScript('grid.js');
		}

		$this->paginator->setItemsPerPage($this->itemsPerPage);
		$this->paginator->setPage($this->page);
		$this->model->setLimit($this->paginator->getLength());
		$this->model->setOffset($this->paginator->getOffset());

		if ($this->sortColumn && $this['columns']->getComponent($this->sortColumn)->isSortable()) {
			$sortByColumn = $this['columns']->getComponent($this->sortColumn);
			$this->model->setSorting($sortByColumn->getColumnName(), $this->sortType);
		} elseif ($this->defaultSortColumn) {
			$this->model->setSorting($this->defaultSortColumn, $this->defaultSortType);
		}

		$page = $this->paginator->getPage();
		if ($this->paginator->getPageCount() < 2) {
			$steps = array($page);
		} else {
			$steps = array($this->paginator->getFirstPage());
			$steps = array_merge($steps, range(
				max($this->paginator->getFirstPage(), $page - 2),
				min($this->paginator->getLastPage(), $page + 2)
			));
			$steps[] = $this->paginator->getLastPage();
			$steps = array_unique($steps);
		}

		$this->template->title           = $title;
		$this->template->paginationSteps = $steps;
		$this->template->itemsPerPage    = $this->itemsPerPage;

		$this->template->render();

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

		$form = new Form();

		if ($this->hasCheckboxes()) {
			$container = $form->addContainer('selected');
			$itemClassMeta = NULL;
			foreach ($this->getModel()->getItems() as $item) {
				if ($itemClassMeta === NULL) {
					$itemClassMeta = $this->em
						->getClassMetadata(get_class($item));
				}
				$this->checkboxes[] = $container->addCheckbox(bin2hex(
					json_encode($itemClassMeta->getIdentifierValues($item))));
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

	public function getCheckbox() {

		static $no = 0;

		return $this->checkboxes[$no++]->control;

	}

	public function setBeforeRemove($callback) {

		$this->beforeRemove = $callback;

	}

	public function getModel() {

		return $this->model;

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

}
