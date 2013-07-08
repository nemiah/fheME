<?php

# OO wrapper for 'php-iban.php'
require_once(dirname(__FILE__) . '/php-iban.php'); # load the procedural codebase
class IBAN {

	function __construct($iban = '') {
		
		$this->iban = $iban;
	}

	public function Verify($iban = '') {
		if ($iban != '') {
			return verify_iban($iban);
		}
		return verify_iban($this->iban);
		# we could throw exceptions of various types, but why - does it really
		# add anything? possibly some slightly better user feedback potential.
		# however, this can be written by hand by performing individual checks
		# ala the code in verify_iban() itself where required, which is likely
		# almost never. for the increased complexity and
		# maintenance/documentation cost, i say, therefore: no. no exceptions.
	}

	public function MistranscriptionSuggestions() {
		return iban_mistranscription_suggestions($this->iban);
	}

	public function MachineFormat() {
		return iban_to_machine_format($this->iban);
	}

	public function HumanFormat() {
		return iban_to_human_format($this->iban);
	}

	public function Country($iban = '') {
		return iban_get_country_part($this->iban);
	}

	public function Checksum($iban = '') {
		return iban_get_checksum_part($this->iban);
	}

	public function BBAN() {
		return iban_get_bban_part($this->iban);
	}

	public function VerifyChecksum() {
		return iban_verify_checksum($this->iban);
	}

	public function FindChecksum() {
		return iban_find_checksum($this->iban);
	}

	public function SetChecksum() {
		$this->iban = iban_set_checksum($this->iban);
	}

	public function ChecksumStringReplace() {
		return iban_checksum_string_replace($this->iban);
	}

	public function Parts() {
		return iban_get_parts($this->iban);
	}

	public function Bank() {
		return iban_get_bank_part($this->iban);
	}

	public function Branch() {
		return iban_get_branch_part($this->iban);
	}

	public function Account() {
		return iban_get_account_part($this->iban);
	}

	public function Countries() {
		return iban_countries();
	}

}

# IBANCountry



?>
