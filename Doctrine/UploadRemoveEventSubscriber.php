<?php

namespace Hnizdil\Doctrine;

use Nette\DI\IContainer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;

class UploadRemoveEventSubscriber
	implements EventSubscriber
{

	protected $container;

	public function __construct(IContainer $container) {

		$this->container = $container;

	}

	public function getSubscribedEvents() {

		return array('onFlush');

	}

	/**
	 * Removes files uploaded with entity.
	 *
	 * @param OnFlushEventArgs $eventArgs
	 * @access public
	 * @return void
	 */
	public function onFlush(OnFlushEventArgs $eventArgs) {

		$em  = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityDeletions() as $entity) {

			$classMeta = $em->getClassMetadata(get_class($entity));

			foreach ($classMeta->fieldMappings as $field => $fieldMeta) {
				if ($fieldMeta['type'] !== 'file') {
					continue;
				}

				$formMeta = $classMeta->formFields[$field];
				if (!$formMeta['uploadDirParam']) {
					continue;
				}

				$uploadDir = $this->container->expand(
					"%{$formMeta['uploadDirParam']}%");
				$filePath  = $uploadDir . '/' . $entity->$field;

				@unlink($filePath);
				@rmdir(dirname($filePath));
			}

		}

	}

}
