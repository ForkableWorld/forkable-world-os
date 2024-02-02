<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\ShellCommands;
use App\Computers\JenkinsSlave;
use App\Files\FileHelper;
use xobotyi\rsync\Exception\Command;
use xobotyi\rsync\Rsync;
class BaseRsyncCommand extends AbstractCommand {
	private Rsync $rsync;
	protected string $destination;
	protected string $source;
	public function __construct(string $src, string $dest, JenkinsSlave $executor = null){
		$rsync = $this->rsync = new Rsync();
		$this->setSource($src);
		$this->setDestination($dest);
		$rsync->setParameters([$src, $dest]);
		parent::__construct(null, null, null, null, 60, false, $executor);
	}
	public function deleteFilesNotInDest(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_DEL, true);
		return $this;
	}
	/**
	 * @return string
	 */
	public function getDestination(): string{
		return $this->destination;
	}
	/**
	 * @return string
	 */
	public function getSource(): string{
		return $this->source;
	}
	public function showStatistics(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_STATS, true);
		return $this;
	}
	public function itemizeChanges(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_ITEMIZE_CHANGES, true);
		return $this;
	}
	public function verbose(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_VERBOSE, true);
		return $this;
	}
	public function recursive(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_RECURSIVE, true);
		return $this;
	}
	public function copySymlinks(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_LINKS, true);
		return $this;
	}
	public function compressDataDuringTransfer(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_COMPRESS, true);
		return $this;
	}
	public function humanReadableOutput(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_HUMAN_READABLE, true);
		return $this;
	}
	public function showProgress(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_PROGRESS, true);
		return $this;
	}
	/**
	 * @return $this
	 * @throws Command
	 * With this option, preexisting destination files are renamed as each file is transferred or deleted.
	 * You can control where the backup file goes and what (if any) suffix gets appended using the --backup-dir and --suffix options.
	 */
	public function backupExistingFilesToDirectory(string $backupDir, string $suffix): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_BACKUP, true);
		$this->getRsync()->setOption(Rsync::OPT_BACKUP_DIR, $backupDir);
		return $this;
	}
	/**
	 * @return $this
	 * @throws Command
	 * With this option, preexisting destination files are renamed as each file is transferred or deleted.
	 * You can control where the backup file goes and what (if any) suffix gets appended using the --backup-dir and --suffix options.
	 */
	public function backupExistingFilesByRenaming(string $suffix): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_BACKUP, true);
		$this->getRsync()->setOption(Rsync::OPT_SUFFIX, $suffix);
		return $this;
	}
	public function preservePermissions(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_PERMS, true);
		return $this;
	}
	public function preserveTimes(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_PERMS, true);
		return $this;
	}
	public function preserveGroup(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_GROUP, true);
		return $this;
	}
	public function preserveOwner(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_OWNER, true);
		return $this;
	}
	public function preserveDeviceFiles(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_DEVICES, true);
		return $this;
	}
	public function preserveSpecialFiles(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_SPECIALS, true);
		return $this;
	}
	public function archive(): BaseRsyncCommand{
		$this->getRsync()->setOption(Rsync::OPT_ARCHIVE, true);
		$this->recursive();
		$this->copySymlinks();
		$this->preservePermissions();
		$this->preserveTimes();
		$this->preserveGroup();
		$this->preserveOwner();
		$this->preserveDeviceFiles();
		$this->preserveSpecialFiles();
		return $this;
	}
	public function getDefinedCommand(): string{
		return $this->getRsync()->__toString();
	}
	/**
	 * @return Rsync
	 */
	public function getRsync(): Rsync{
		return $this->rsync;
	}
	/**
	 * @param string $dest
	 * @return BaseRsyncCommand
	 */
	public function setDestination(string $dest): self {
		$this->validateDestination($dest);
		$this->destination = $dest;
		return $this;
	}
	/**
	 * @param string $src
	 * @return void
	 */
	private function setSource(string $src): void{
		$this->source = abs_path($src);
	}
	/**
	 * @param string $dest
	 */
	protected function validateDestination(string $dest): void{
		FileHelper::assertAbsPath($dest);
	}
}
