<?php declare(strict_types = 1);

namespace Contributte\CzechPost\XmlRequest;

use Contributte\CzechPost\XmlRequest\Consignment\Consignment;
use Contributte\CzechPost\XmlRequest\Consignment\Enum\PrintType;
use Contributte\CzechPost\XmlRequest\Consignment\File;
use DOMDocument;
use DOMElement;
use DOMNode;

final class ConsignmentRequestFactory
{

	/** @var Consignment */
	private $consignment;

	/** @var DOMDocument */
	private $dom;

	public function create(Consignment $consignment): string
	{
		$this->consignment = $consignment;
		$this->dom = new DOMDocument('1.0', 'UTF-8');

		// create root element
		$root = $this->dom->createElement('dataroot');
		$root = $this->dom->appendChild($root);

		// add mandatory xml sections
		$this->addBasic($root);
		$this->addSender($root);
		$this->addRecipient($root);
		$this->addFiles($root);
		$this->addCheque($root);

		return $this->dom->saveXML();
	}

	private function addBasic(DOMNode $root): void
	{
		$root->appendChild($this->el('typvyplatneho', $this->consignment->getPaymentType()));
		$root->appendChild($this->el('typtisku', $this->consignment->getPrintType()));
		$root->appendChild($this->el('obalkac4', $this->consignment->getEnvelope()));
		$root->appendChild($this->el('tiskpoukazky', $this->consignment->isPrintCheque() ? '1' : '0'));
		$root->appendChild($this->el('typods', $this->consignment->getPrintSenderType()));
		$root->appendChild($this->el('typadr', $this->consignment->getPrintRecipientType()));
	}

	private function addSender(DOMNode $root): void
	{
		$s = $this->consignment->getSender();

		$root->appendChild($this->el('odsfirma', $s->getCompany()));
		$root->appendChild($this->el('odsosoba', $s->getFullName()));
		$root->appendChild($this->el('odsulice', $s->getStreet()));
		$root->appendChild($this->el('odscp', $s->getStreetNumber()));
		$root->appendChild($this->el('odsco', $s->getOrientationNumber()));
		$root->appendChild($this->el('odsobec', $s->getMunicipality()));
		$root->appendChild($this->el('odspsc', $s->getPostcode()));

		if ($this->consignment->getPrintSenderType() === PrintType::SENDER_USE_CUSTOM_IMAGE) {
			$root->appendChild($this->el('odsid', 'TODO')); // Identifikace obrázku dodaného od zákazníka
			$root->appendChild($this->el('odsobrazek', 'TODO')); // Soubor s logem, kódován pomocí algoritmu Base64
		} else {
			$root->appendChild($this->el('odsid', ''));
			$root->appendChild($this->el('odsobrazek', ''));
		}
	}

	private function addRecipient(DOMNode $root): void
	{
		$r = $this->consignment->getRecipient();

		$root->appendChild($this->el('adrosloveni', $r->getSalutation()));
		$root->appendChild($this->el('adrfirma', $r->getCompany()));
		$root->appendChild($this->el('adrosoba', $r->getFullName()));
		$root->appendChild($this->el('adrulice', $r->getStreet()));
		$root->appendChild($this->el('adrcp', $r->getStreetNumber()));
		$root->appendChild($this->el('adrco', $r->getOrientationNumber()));
		$root->appendChild($this->el('adrobec', $r->getMunicipality()));
		$root->appendChild($this->el('adrpsc', $r->getPostcode()));
		$root->appendChild($this->el('adriso', $r->getCountry()));
	}

	private function addCheque(DOMNode $root): void
	{
		$ch = $this->consignment->getCheque();
		if ($ch === null) {
			return;
		}

		$cheque = $this->dom->createElement('poukazka');
		$root->appendChild($cheque);

		$cheque->appendChild($this->el('castka', $ch->getPrice()));
		$cheque->appendChild($this->el('predcisli_uctu', $ch->getBankAccountPrefix()));
		$cheque->appendChild($this->el('ucet', $ch->getBankAccountNumber()));
		$cheque->appendChild($this->el('kod_banky', $ch->getBankCode()));
		$cheque->appendChild($this->el('variabilni_symbol', $ch->getVariableSymbol()));
		$cheque->appendChild($this->el('specificky_symbol', $ch->getSpecificSymbol()));
		$cheque->appendChild($this->el('konstantni_symbol', $ch->getConstantSymbol()));
		$cheque->appendChild($this->el('zprava_pro_prijemce1', $ch->getCommentLineOne()));
		$cheque->appendChild($this->el('zprava_pro_prijemce2', $ch->getCommentLineTwo()));
		$cheque->appendChild($this->el('ucel_platby', $ch->getPurpose()));

		$accountOwnerAddr = $this->dom->createElement('adresa_majitele_uctu');
		foreach ($ch->getRecipientAddressLines() as $key => $line) {
			$accountOwnerAddr->appendChild($this->el('adr' . ((int) $key + 1), $line));
		}
		$cheque->appendChild($accountOwnerAddr);

		$senderAddr = $this->dom->createElement('odesilatel');
		foreach ($ch->getRecipientAddressLines() as $key => $line) {
			$senderAddr->appendChild($this->el('adr' . ((int) $key + 1), $line));
		}
		$cheque->appendChild($senderAddr);
	}

	private function addFiles(DOMNode $root): void
	{
		if (!$this->consignment->hasFiles()) {
			return;
		}

		$filesElement = $this->dom->createElement('soubory');
		$root->appendChild($filesElement);

		foreach ($this->consignment->getFiles() as $file) {
			$filesElement->appendChild($this->createFile($file));
		}
	}

	private function createFile(File $file): DOMElement
	{
		$f = $this->dom->createElement('soubor');
		$f->setAttribute('mimeType', '');
		$f->setAttribute('name', $file->getFileName());
		$f->appendChild($this->el('typtisku', $file->getPrintType()));
		$f->appendChild($this->el('dataSoubor', $file->getContent()));

		return $f;
	}

	/**
	 * @param string|int|float $value
	 */
	private function el(string $name, $value): DOMElement
	{
		return $this->dom->createElement($name, addslashes((string) $value));
	}

}
