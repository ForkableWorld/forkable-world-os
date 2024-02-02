<?php
namespace Tests\UnitTests\Computers;
use App\Computers\JenkinsSlave;
class CypressComputer extends JenkinsSlave {
	public function needToReboot(): ?string{
		return $this->sshOffline();
	}
	public function isWeb(): bool{
		return false;
	}
}
