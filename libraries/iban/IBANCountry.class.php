<?php
require_once(dirname(__FILE__) . '/php-iban.php'); # load the procedural codebase
class IBANCountry {
	# constructor with code

	function __construct($code = '') {
		$this->code = $code;
	}

	public function Name() {
		return iban_country_get_country_name($this->code);
	}

	public function DomesticExample() {
		return iban_country_get_domestic_example($this->code);
	}

	public function BBANExample() {
		return iban_country_get_bban_example($this->code);
	}

	public function BBANFormatSWIFT() {
		return iban_country_get_bban_format_swift($this->code);
	}

	public function BBANFormatRegex() {
		return iban_country_get_bban_format_regex($this->code);
	}

	public function BBANLength() {
		return iban_country_get_bban_length($this->code);
	}

	public function IBANExample() {
		return iban_country_get_iban_example($this->code);
	}

	public function IBANFormatSWIFT() {
		return iban_country_get_iban_format_swift($this->code);
	}

	public function IBANFormatRegex() {
		return iban_country_get_iban_format_regex($this->code);
	}

	public function IBANLength() {
		return iban_country_get_iban_length($this->code);
	}

	public function BankIDStartOffset() {
		return iban_country_get_bankid_start_offset($this->code);
	}

	public function BankIDStopOffset() {
		return iban_country_get_bankid_stop_offset($this->code);
	}

	public function BranchIDStartOffset() {
		return iban_country_get_branchid_start_offset($this->code);
	}

	public function BranchIDStopOffset() {
		return iban_country_get_branchid_stop_offset($this->code);
	}

	public function RegistryEdition() {
		return iban_country_get_registry_edition($this->code);
	}

	public function IsSEPA() {
		return iban_country_is_sepa($this->code);
	}

}
?>