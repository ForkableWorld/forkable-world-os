<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Slack;
use App\Buttons\Admin\JenkinsConsoleButton;
use App\Buttons\Admin\JenkinsJobButton;
use App\Buttons\Admin\PHPUnitButton;
use App\Buttons\UrlButton;
use App\Exceptions\InvalidStringException;
use App\PhpUnitJobs\JobTestCase;
use App\Repos\QMAPIRepo;
use App\Slim\Model\Notifications\SlackNotification;
use App\Types\QMStr;
use App\Utils\AppMode;
use Maknz\Slack\Attachment;
use Maknz\Slack\Message;
use Tests\QMBaseTestCase;

class SlackMessage extends Message {
	public const SLACK_CHANNEL = "#emergency";
	/**
	 * @param string $username
	 */
	public function __construct(string $username){
		parent::__construct(SlackNotification::slackClient());
		$this->addPHPUnitTestAttachment();
		$this->addAPIUrlAttachment();
		if(empty($username)){
			$username = $this->generateFallbackUserName();
		}
		$this->setUsername($username);
		$this->setAllowMarkdown(true);
		$this->setChannel(static::SLACK_CHANNEL);
	}
	/**
	 * @param Attachment $attachment
	 * @return Message
	 */
	public function attach($attachment){
		/** @var Attachment $previous */
		foreach($this->attachments as $i => $previous){
			$currentText = $attachment->getTitle();
			$previousText = $previous->getTitle();
			if(empty($currentText)){
				throw new \LogicException("No attachment Title!");
			}
			QMStr::assertIsUrl($attachment->getTitleLink(), get_class($attachment));
			if($previousText === $currentText){
				$this->attachments[$i] = $attachment;
				return $this;
			}
		}
		return parent::attach($attachment);
	}
	public function validatePayload(){
		$payload = $this->client->preparePayload($this);
		$encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);
		if(!$encoded){
			throw new \LogicException("Could not json encode payload because: " . json_last_error_msg() .
				\App\Logging\QMLog::print_r($payload, true));
		}
		return $encoded;
	}
	public function send($text = null){
		parent::send($text);
	}
	public function addJenkinsAttachments(): void{
		if(!AppMode::isJenkins()){
			return;
		}
		$this->attach((new JenkinsJobButton())->getSlackAttachment());
		$this->attach((new JenkinsConsoleButton())->getSlackAttachment());
	}
	/**
	 * @param SlackMessage $m
	 */
	public function addGithubAttachments(): void{
		$this->attach(QMAPIRepo::getCommitButton()->getSlackAttachment());
		$this->attach(QMAPIRepo::getBranchButton()->getSlackAttachment());
	}
	/**
	 * @return string|null
	 */
	private function generateFallbackUserName(){
		$username = \App\Utils\AppMode::getCurrentTestName();
		$test = \App\Utils\AppMode::getCurrentTestName();
		$job = JobTestCase::getJobTaskOrTestName();
		if($test && stripos($username, $test) === false){
			if(empty($username)){
				$username = $test;
			} else{
				$username .= " from $test";
			}
		} elseif($job && stripos($username, $job) === false){
			if(empty($username)){
				$username = $job;
			} else{
				$username .= " from $job";
			}
		}
		$branch = QMAPIRepo::getBranchFromMemory();
		if($branch && $branch !== "develop" && stripos($username, $branch) === false){
			$username .= " on branch $branch";
		}
		return $username;
	}
	private function addPHPUnitTestAttachment(): void{
		$b = PHPUnitButton::getForCurrentTest();
		if($b){
			$this->attach($b->getSlackAttachment());
		}
	}
	private function addAPIUrlAttachment(): void{
		if(AppMode::isApiRequest()){
			$this->attach((new UrlButton())->getSlackAttachment());
		}
	}
	/**
	 * Set the message text
	 * @param string $text
	 * @return Message|SlackMessage
	 * @throws InvalidStringException
	 */
	public function setText($text){
		if(empty($text)){
			throw new \LogicException("No text provided to setText on " . get_class($this));
		}
		QMStr::assertStringShorterThan(40000, $text, "slack message");
		$after = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
		$this->text = $after;
		$this->validatePayload();
		return $this;
	}
}
