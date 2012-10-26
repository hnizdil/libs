<?php

namespace Hnizdil\Nette\Mail;

use Nette\Utils\Html;
use Nette\Mail\Message;

/**
 * Přidává na konec e-mailu původní adresáty.
 */
class DevelMessage
	extends Message
{

	private $recipient;

	protected function build() {

		// vytvoření HTML s původními adresáty
		$html = Html::el()->add(Html::el('hr'));
		$this->addRecipientList($html, $this, 'To');
		$this->addRecipientList($html, $this, 'Cc');
		$this->addRecipientList($html, $this, 'Bcc');
		$this->setHtmlBody($this->getHtmlBody() . $html);

		// nastavení jediného adresáta zprávy
		$mail = parent::build();
		$mail->clearHeader('To');
		$mail->clearHeader('Cc');
		$mail->clearHeader('Bcc');
		$mail->addTo($this->recipient);

		return $mail;

	}

	private function addRecipientList($html, $mail, $header) {

		$list = $mail->getHeader($header);

		if ($list) {
			$html->add(Html::el('p')->setText("{$header}:"));
			$html->add($ul = Html::el('ul'));
			foreach ($list as $email => $name) {
				$ul->add(Html::el('li')
					->setText($name ? "{$name} <{$email}>" : $email));
			}
		}

	}

	public function setRecipient($recipient) {

		$this->recipient = $recipient;

	}

}
