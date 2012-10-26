<?php

namespace Hnizdil\Gridito;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\SubmitButton;
use Hnizdil\Nette\Forms\Controls\DateInput;
use Doctrine\ORM\QueryBuilder;

class FilterForm
	extends Form
{

	/**
	 * @var array
	 * @access public
	 * @persistent
	 */
	public $values = array();

	private $defaultFilter = array();

	private $prepareMethod = NULL;

	/**
	 * přidává tlačítka odeslání a vymazání formuláře
	 *
	 * @param mixed $presenter 
	 * @access public
	 * @return void
	 */
	public function attached($presenter) {

		parent::attached($presenter);

		if ($presenter instanceof Presenter) {

			$this->addSubmit('submit', 'Hledat')->onClick[] =
				function(SubmitButton $button) use ($presenter) {
					$presenter['grid']->filter = $button->form->getValues(TRUE);
					$presenter->redirect('this');
				};

			$this->addSubmit('reset', 'Vymazat')->onClick[] =
				function(SubmitButton $button) use ($presenter) {
					$presenter['grid']->filter = array();
					$presenter->redirect('this');
				};

			$this['submit']->getControlPrototype()->class('find');
			$this['reset']->getControlPrototype()->class('clear');

			// selectboxy jsou automaticky nepovinné
			$selectBoxes = $this->getComponents(
				TRUE, 'Nette\\Forms\\Controls\\SelectBox');
			foreach ($selectBoxes as $selectbox) {
				$selectbox->setPrompt('');
			}

			// defaultní filtrování
			$filter =& $presenter['grid']->filter;
			if ($this->defaultFilter && !$filter) {
				$filter = $this->defaultFilter;
			}

		}

	}

	public function apply(QueryBuilder $qb) {

		if (is_callable($this->prepareMethod)) {
			call_user_func($this->prepareMethod, $qb);
		}

		$this->addConditions($qb);

		return $qb;

	}

	private function addConditions(QueryBuilder $qb) {

		$conditions = array();

		$controls = $this->getComponents(
			TRUE, 'Nette\\Forms\\Controls\\BaseControl');

		foreach ($controls as $name => $control) {

			$value = $control->getValue();

			if ($control instanceof SubmitButton) {
				continue;
			}

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
				$qb->setParameter($paramName, "%{$control->getValue()}%");
			}

			elseif ($control instanceof DateInput) {
				if ($operator == 'lte' || $operator == 'gte') {
					foreach ($fields as $fieldName) {
						$orX[] = $qb->expr()
							->$operator($fieldName, ':' . $paramName);
					}
					$qb->setParameter($paramName,
						$control->getValue()->format('Y-m-d '
							. ($operator == 'lte' ? '23:59:59' : '00:00:00')));
				}
				elseif ($fieldType == 'datetime') {
					foreach ($fields as $fieldName) {
						$orX[] = $qb->expr()->between($fieldName,
							":{$paramName}start", ":{$paramName}end");
					}
					$qb->setParameter(":{$paramName}start",
						$control->getValue()->format('Y-m-d 00:00:00'));
					$qb->setParameter(":{$paramName}end",
						$control->getValue()->format('Y-m-d 23:59:59'));
				}
				else {
					foreach ($fields as $fieldName) {
						$orX[] = $qb->expr()->eq($fieldName, ':' . $paramName);
					}
					$qb->setParameter($paramName,
						$control->getValue()->format('Y-m-d'));
				}
			}

			else {
				foreach ($fields as $fieldName) {
					$orX[] = $qb->expr()->eq($fieldName, ':' . $paramName);
				}
				$qb->setParameter($paramName, $control->getValue());
			}

			$conditions[] = call_user_func_array(
				array($qb->expr(), 'orX'), $orX);

		}

		if ($conditions) {
			$qb->andWhere(call_user_func_array(
				array($qb->expr(), 'andX'), $conditions));
		}

	}

	public function setPrepareMethod($prepareMethod) {

		$this->prepareMethod = $prepareMethod;

	}

	public function setDefaultFilter(array $defaultFilter) {

		$this->defaultFilter = $defaultFilter;

	}

}
