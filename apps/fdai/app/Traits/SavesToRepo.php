<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\GitAlreadyUpToDateException;
use App\Exceptions\GitBranchAlreadyExistsException;
use App\Exceptions\GitBranchNotFoundException;
use App\Exceptions\GitConflictException;
use App\Exceptions\GitLockException;
use App\Exceptions\GitNoStashException;
use App\Exceptions\GitRepoAlreadyExistsException;
use App\Repos\CCStudiesRepo;
use Symfony\Component\Debug\Exception\OutOfMemoryException;
trait SavesToRepo {
	public function saveToRepoAndCommit(){
		$this->saveToRepo();
		try {
			CCStudiesRepo::commitAndPush("Updated " . $this->getTitleAttribute());
		} catch (GitAlreadyUpToDateException | OutOfMemoryException | GitRepoAlreadyExistsException | GitNoStashException | GitLockException | GitConflictException | GitBranchNotFoundException | GitBranchAlreadyExistsException $e) {
			le($e);
		}
	}
	public function saveToRepo(){
		$html = $this->getShowPageHtml();
		CCStudiesRepo::writeHtml($this->getStaticRepoFolder() . "/index.html", $html);
		$this->saveChartsToRepo();
	}
	public function getStaticRepoFolder(): string{
		$folderPath = static::getPluralizedSlugifiedClassName();
		return $folderPath . "/" . $this->getSlug();
	}
}
