<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SpellCheckingInspection */
namespace App\Wiki;
use LogicException;
/**
 * This class is interacts with wikipedia using api.php
 * @author Chris G and Cobi
 **/
class MediaWikiBot {
	protected $pageTitles;
	protected $pass;
	protected $user;
	private $token;
	private $ecTimestamp;
	public $url;
	private $ch;
	private $uid;
	public $cookie_jar;
	public $postfollowredirs;
	public $getfollowredirs;
	public $quiet=false;
	/**
	 * This is our constructor.
	 * @return void
	 **/
	function __construct($url = 'https://mediawiki.crowsourcingcures.org/w/api.php', string $uname = null, string
	$pwd = null){
		$this->ch = curl_init();
		$this->uid = dechex(rand(0,99999999));
		curl_setopt($this->ch,CURLOPT_COOKIEJAR,'/tmp/cluewikibot.cookies.'.$this->uid.'.dat');
		curl_setopt($this->ch,CURLOPT_COOKIEFILE,'/tmp/cluewikibot.cookies.'.$this->uid.'.dat');
		curl_setopt($this->ch,CURLOPT_MAXCONNECTS,100);
		$this->postfollowredirs = 0;
		$this->getfollowredirs = 1;
		$this->cookie_jar = [];
		$this->token = null;
		$this->url = $url;
		$this->ecTimestamp = null;
		if($uname !== null) $this->setHTTPcreds($uname ?? \App\Utils\Env::get('MEDIAWIKI_USER'), $pwd ?? 'MEDIAWIKI_PW');
	}
	/**
	 * @return string
	 */
	public function getPass(): string {
		return $this->pass;
	}
	/**
	 * @return string
	 */
	public function getUser(): string {
		return $this->user;
	}
	/**
	 * @return mixed
	 */
	public function http_code () {
		return curl_getinfo( $this->ch, CURLINFO_HTTP_CODE );
	}
	/**
	 * @param array $data
	 * @param string $keyprefix
	 * @param string $keypostfix
	 * @return null|string
	 */
	function data_encode (array $data, string $keyprefix = "", string $keypostfix = ""): ?string{
		$vars=null;
		foreach($data as $key=>$value) {
			if(is_array($value))
				$vars .= $this->data_encode($value, $keyprefix.$key.$keypostfix.urlencode("["), urlencode("]"));
			else
				$vars .= $keyprefix.$key.$keypostfix."=".urlencode($value)."&";
		}
		return $vars;
	}
	/**
	 * @param string $url
	 * @param string|array $data
	 * @return bool|string
	 */
	function post (string $url, $data) {
		//echo 'POST: '.$url."\n";
		$time = $this->getTime($url);
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->postfollowredirs);
		curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['Expect:']);
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
		curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($this->ch,CURLOPT_POST,1);
		//      curl_setopt($this->ch,CURLOPT_FAILONERROR,1);
		//	curl_setopt($this->ch,CURLOPT_POSTFIELDS, substr($this->data_encode($data), 0, -1) );
		curl_setopt($this->ch,CURLOPT_POSTFIELDS, $data);
		$data = curl_exec($this->ch);
		//	echo "Error: ".curl_error($this->ch);
		//	var_dump($data);
		//	global $logfd;
		//	if (!is_resource($logfd)) {
		//		$logfd = fopen('php://stderr','w');
		if (!$this->quiet)
			echo 'POST: '.$url.' ('.(microtime(1) - $time).' s) ('.strlen($data)." b)\n";
		// 	}
		return $data;
	}
	/**
	 * @param $url
	 * @return string
	 */
	function get ($url): string{
		//echo 'GET: '.$url."\n";
		$time = $this->getTime($url);
		curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,$this->getfollowredirs);
		curl_setopt($this->ch,CURLOPT_MAXREDIRS,10);
		curl_setopt($this->ch,CURLOPT_HEADER,0);
		curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($this->ch,CURLOPT_TIMEOUT,30);
		curl_setopt($this->ch,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($this->ch,CURLOPT_HTTPGET,1);
		//curl_setopt($this->ch,CURLOPT_FAILONERROR,1);
		$data = curl_exec($this->ch);
		//echo "Error: ".curl_error($this->ch);
		//var_dump($data);
		//global $logfd;
		//if (!is_resource($logfd)) {
		//    $logfd = fopen('php://stderr','w');
		if (!$this->quiet)
			echo 'GET: '.$url.' ('.(microtime(1) - $time).' s) ('.strlen($data)." b)\n";
		//}
		return $data;
	}
	/**
	 * @param string $uname
	 * @param string $pwd
	 */
	function setHTTPcreds(string $uname, string $pwd) {
		$this->user = $uname;
		$this->pass = $pwd;
		curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($this->ch, CURLOPT_USERPWD, $uname.":".$pwd);
	}

	function __destruct () {
		curl_close($this->ch);
		@unlink('/tmp/cluewikibot.cookies.'.$this->uid.'.dat');
	}

	/**
	 * @param $var
	 * @param $val
	 */
	function __set($var, $val){
		switch($var) {
			case 'quiet':
				$this->quiet = $val;
				break;
			default:
				echo "WARNING: Unknown variable ($var)!\n";
		}
	}
	/**
	 * Sends a query to the api.
	 * @param string $query The query string.
	 * @param string|array|null $post POST data if its a post request (optional).
	 * @param int $repeat how many times we've repeated this request
	 * @return array The api result.string $page
	 **/
	function query(string $query, $post = null, int $repeat = 0): array{
		global $AssumeHTTPFailuresAreJustTimeoutsAndShouldBeSuppressed;
		if($post == null){
			$ret = $this->get($this->url . $query);
		} else{
			$ret = $this->post($this->url . $query, $post);
		}
		//var_dump($this->http_code());
		if($this->http_code() == 0 && $AssumeHTTPFailuresAreJustTimeoutsAndShouldBeSuppressed){
			return []; // Meh
		}
		if($this->http_code() != "200"){
			if($repeat < 10){
				return $this->query($query, $post, ++$repeat);
			} else{
				throw new LogicException("HTTP Error.");
			}
		}
		return json_decode($ret, true);
	}
	/**
	 * Gets the content of a page. Returns false on error.
	 * @param string $title The wikipedia page to fetch.
	 * @param int|null $revid The revision id to fetch (optional)
	 * @param bool $detectEditConflict
	 * @return false|string
	 */
	function getpage(string $title, int $revid = null, bool $detectEditConflict = false){
		$append = '';
		if($revid != null) $append = '&rvstartid=' . $revid;
		$x = $this->query('?action=query&format=json&prop=revisions&titles=' . urlencode($title) .
			'&rvlimit=1&rvprop=content|timestamp' . $append);
		foreach($x['query']['pages'] as $ret){
			if(isset($ret['revisions'][0]['*'])){
				if($detectEditConflict) $this->ecTimestamp = $ret['revisions'][0]['timestamp'];
				return $ret['revisions'][0]['*'];
			} else
				return false;
		}
		le("what?");throw new \LogicException();
	}
	/**
	 * Gets the page id for a page.
	 * @param string $title The wikipedia page to get the id for.
	 * @return int The page id of the page.
	 */
	function getpageid(string $title): int{
		$x = $this->query('?action=query&format=json&prop=revisions&titles=' . urlencode($title) .
			'&rvlimit=1&rvprop=content');
		foreach($x['query']['pages'] as $ret){
			return $ret['pageid'];
		}
		le("what?");
	}
	/**
	 * Gets the number of contributions a user has.
	 * @param string $user The username for which to get the edit count.
	 * @return int The number of contributions the user has.
	 */
	function contribcount(string $user): int{
		$x = $this->query('?action=query&list=allusers&format=json&auprop=editcount&aulimit=1&aufrom=' .
			urlencode($user));
		return $x['query']['allusers'][0]['editcount'];
	}
	/**
	 * Returns an array with all the members of $category
	 * @param string $category The category to use.
	 * @param string|null $subcat (bool) Go into sub categories?
	 * @return array|false
	 */
	function categorymembers(string $category, string $subcat = null){
		$continue = '&rawcontinue=';
		$pages = [];
		while(true){
			$res = $this->query('?action=query&list=categorymembers&cmtitle=' . urlencode($category) .
				'&format=json&cmlimit=500' . $continue);
			if(isset($x['error'])){
				return false;
			}
			foreach($res['query']['categorymembers'] as $x){
				$pages[] = $x['title'];
			}
			if(empty($res['query-continue']['categorymembers']['cmcontinue'])){
				if($subcat){
					foreach($pages as $p){
						if(substr($p, 0, 9) == 'Category:'){
							$pages2 = $this->categorymembers($p, true);
							$pages = array_merge($pages, $pages2);
						}
					}
				}
				return $pages;
			} else{
				$continue =
					'&rawcontinue=&cmcontinue=' . urlencode($res['query-continue']['categorymembers']['cmcontinue']);
			}
		}
	}
	/**
	 * Returns the number of pages in a category
	 * @param string $category The category to use (including prefix)
	 * @return integer
	 */
	function categorypagecount(string $category): int{
		$res = $this->query('?action=query&format=json&titles=' . urlencode($category) .
			'&prop=categoryinfo&formatversion=2');
		return $res['query']['pages'][0]['categoryinfo']['pages'];
	}
	/**
	 * Returns a list of pages that link to $page.
	 * @param string $title
	 * @param array|null $extra (defaults to null)
	 * @return array|false
	 */
	function whatlinkshere(string $title, array $extra = null){
		$continue = '&rawcontinue=';
		$pages = [];
		while(true){
			$res =
				$this->query('?action=query&list=backlinks&bltitle=' . urlencode($title) . '&bllimit=500&format=json' .
					$continue . $extra);
			if(isset($res['error'])){
				return false;
			}
			foreach($res['query']['backlinks'] as $x){
				$titles[] = $x['title'];
			}
			if(empty($res['query-continue']['backlinks']['blcontinue'])){
				return $pages;
			} else{
				$continue = '&rawcontinue=&blcontinue=' . urlencode($res['query-continue']['backlinks']['blcontinue']);
			}
		}
	}
	/**
	 * Returns a list of pages that include the image.
	 * @param $image
	 * @param array|null $extra
	 * @return array|false
	 */
	function whereisincluded($image, array $extra = null){
		$continue = '&rawcontinue=';
		$pages = [];
		while(true){
			$res =
				$this->query('?action=query&list=imageusage&iutitle=' . urlencode($image) . '&iulimit=500&format=json' .
					$continue . ($extra ?? null));
			if(isset($res['error'])) return false;
			foreach($res['query']['imageusage'] as $x){
				$pages[] = $x['title'];
			}
			if(empty($res['query-continue']['imageusage']['iucontinue'])) return $pages; else
				$continue = '&rawcontinue=&iucontinue=' . urlencode($res['query-continue']['imageusage']['iucontinue']);
		}
	}
	/**
	 * Returns a list of pages that use the $template.
	 * @param string $template the template we are intereste into
	 * @param array|null $extra (defaults to null)
	 * @return array|false
	 */
	function whatusethetemplate(string $template, array $extra = null){
		$continue = '&rawcontinue=';
		$pages = [];
		while(true){
			$res = $this->query('?action=query&list=embeddedin&eititle=Template:' . urlencode($template) .
				'&eilimit=500&format=json' . $continue . $extra);
			if(isset($res['error'])){
				return false;
			}
			foreach($res['query']['embeddedin'] as $x){
				$pages[] = $x['title'];
			}
			if(empty($res['query-continue']['embeddedin']['eicontinue'])){
				return $pages;
			} else{
				$continue = '&rawcontinue=&eicontinue=' . urlencode($res['query-continue']['embeddedin']['eicontinue']);
			}
		}
	}
	/**
	 * Returns an array with all the subpages of $title
	 * @param string $title
	 * @return array|false
	 */
	function subpages(string $title){
		/* Calculate all the namespace codes */
		$ret = $this->query('?action=query&meta=siteinfo&siprop=namespaces&format=json');
		foreach($ret['query']['namespaces'] as $x){
			$namespaces[$x['*']] = $x['id'];
		}
		$temp = explode(':', $title, 2);
		$namespace = $namespaces[$temp[0]];
		$title = $temp[1];
		$continue = '&rawcontinue=';
		$subpages = [];
		while(true){
			$res = $this->query('?action=query&format=json&list=allpages&apprefix=' . urlencode($title) .
				'&aplimit=500&apnamespace=' . $namespace . $continue);
			if(isset($x['error'])){
				return false;
			}
			foreach($res['query']['allpages'] as $p){
				$subpages[] = $p['title'];
			}
			if(empty($res['query-continue']['allpages']['apfrom'])){
				return $subpages;
			} else{
				$continue = '&rawcontinue=&apfrom=' . urlencode($res['query-continue']['allpages']['apfrom']);
			}
		}
	}
	/**
	 * This function takes a username and password and logs you into wikipedia.
	 * @return array
	 */
	function login(): array{
		$post = ['lgname' => $this->user, 'lgpassword' => $this->pass];
		$ret = $this->query('?action=login&format=json', $post);
		/* This is now required - see https://bugzilla.wikimedia.org/show_bug.cgi?id=23076 */
		if($ret['login']['result'] == 'NeedToken'){
			$post['lgtoken'] = $ret['login']['token'];
			$ret = $this->query('?action=login&format=json', $post);
		}
		if($ret['login']['result'] != 'Success'){
			echo "Login error: \n";
			\App\Logging\QMLog::print_r($ret);
			die();
		} else{
			return $ret;
		}
	}

	/* crappy hack to allow users to use cookies from old sessions */
	/**
	 * @param array $data
	 */
	function setLogin(array $data){
		$this->cookie_jar = [
			$data['cookieprefix'] . 'UserName' => $data['lgusername'],
			$data['cookieprefix'] . 'UserID' => $data['lguserid'],
			$data['cookieprefix'] . 'Token' => $data['lgtoken'],
			$data['cookieprefix'] . '_session' => $data['sessionid'],
		];
	}
	/**
	 * Check if we're allowed to edit $title.
	 * See http://en.wikipedia.org/wiki/Template:Bots
	 * for more info.
	 * @param string $title The page we want to edit.
	 * @param string|null $user The bot's username.
	 * @param string|null $text page text, will override page
	 * @return bool true if we can edit
	 **/
	function nobots(string $title, string $user = null, string $text = null): bool{
		if($text == null){
			$text = $this->getpage($title);
		}
		if($user != null){
			if(preg_match('/{{(nobots|bots\|allow=none|bots\|deny=all|bots\|optout=all|bots\|deny=.*?' .
				preg_quote($user, '/') . '.*?)}}/iS', $text)){
				return false;
			}
		} else{
			if(preg_match('/{{(nobots|bots\|allow=none|bots\|deny=all|bots\|optout=all)}}/iS', $text)){
				return false;
			}
		}
		return true;
	}
	/**
	 * This function returns the edit token for the current user.
	 * @return string edit token.
	 */
	function getedittoken(): string{
		$x = $this->query('?action=query&meta=tokens&format=json');
		return $x['query']['tokens']['csrftoken'];
	}
	/**
	 * Purges the cache of $title.
	 * @param string $title The page to purge.
	 * @return array
	 */
	function purgeCache(string $title): array{
		return $this->query('?action=purge&titles=' . urlencode($title) . '&format=json');
	}
	/**
	 * Checks if $user has email enabled.
	 * Uses index.php.
	 * @param string $user The user to check.
	 * @return bool.
	 */
	function checkEmail(string $user): bool{
		$x = $this->query('?action=query&meta=allmessages&ammessages=noemailtext|notargettext&amlang=en&format=json');
		$messages[0] = $x['query']['allmessages'][0]['*'];
		$messages[1] = $x['query']['allmessages'][1]['*'];
		$page = $this->get(str_replace('api.php', 'index.php', $this->url) . '?title=Special:EmailUser&target=' .
			urlencode($user));
		if(preg_match('/(' . preg_quote($messages[0], '/') . '' . preg_quote($messages[1], '/') . ')/i', $page)){
			return false;
		} else{
			return true;
		}
	}
	/**
	 * Returns all the pages $title is transcluded on.
	 * @param string $title The page to get the transclusions from.
	 * @param int|null $sleep The time to sleep between requets (set to null to disable).
	 * @param array|null $extra
	 * @return array.
	 */
	function getTransclusions(string $title, int $sleep = null, array $extra = null): array{
		$continue = '&rawcontinue=';
		$pages = [];
		while(true){
			$ret = $this->query('?action=query&list=embeddedin&eititle=' . urlencode($title) . $continue . $extra .
				'&eilimit=500&format=json');
			if($sleep != null){
				sleep($sleep);
			}
			foreach($ret['query']['embeddedin'] as $x){
				$pages[] = $x['title'];
			}
			if(isset($ret['query-continue']['embeddedin']['eicontinue'])){
				$continue = '&rawcontinue=&eicontinue=' . $ret['query-continue']['embeddedin']['eicontinue'];
			} else{
				return $pages;
			}
		}
	}
	/**
	 * Edits a page.
	 * @param string $title Page name to edit.
	 * @param string $newContent Data to post to page.
	 * @param string $summary Edit summary to use.
	 * @param bool $minor Whether or not to mark edit as minor.  (Default false)
	 * @param bool $bot Whether or not to mark edit as a bot edit.  (Default true)
	 * @param string|null $section
	 * @param bool $detectEC
	 * @param string $maxlag
	 * @return array
	 */
	function edit(string $title, string $newContent, string $summary = '', bool $minor = false, bool $bot = true, string
	$section = null, bool $detectEC = false,
		string $maxlag = ''): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'title' => $title,
			'text' => $newContent,
			'token' => $this->token,
			'summary' => $summary,
			($minor ? 'minor' : 'notminor') => '1',
			($bot ? 'bot' : 'notbot') => '1',
		];
		if($section != null){
			$params['section'] = $section;
		}
		if($this->ecTimestamp != null && $detectEC == true){
			$params['basetimestamp'] = $this->ecTimestamp;
			$this->ecTimestamp = null;
		}
		if($maxlag != ''){
			$maxlag = '&maxlag=' . $maxlag;
		}
		return $this->query('?action=edit&format=json' . $maxlag, $params);
	}
	/**
	 * Add a text at the bottom of a page
	 * @param string $title page we're working with.
	 * @param string $text text that you want to add.
	 * @param string $summary summary to use.
	 * @param bool $minor or not to mark edit as minor.  (Default false)
	 * @param bool $bot or not to mark edit as a bot edit.  (Default true)
	 * @param array result
	 **/
	function addtext(string $title, string $text, string $summary = '', bool $minor = false, bool $bot = true): array{
		$page = $this->getpage($title);
		$page .= "\n" . $text;
		return $this->edit($title, $page, $summary, $minor, $bot);
	}
	/**
	 * Moves a page.
	 * @param string $old Name of page to move.
	 * @param string $new New page title.
	 * @param string $reason  Move summary to use.
	 * @param string|null $options
	 * @return array
	 */
	function move(string $old, string $new, string $reason, string $options = null): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'from' => $old,
			'to' => $new,
			'token' => $this->token,
			'reason' => $reason,
		];
		if($options != null){
			$option = explode('|', $options);
			foreach($option as $o){
				$params[$o] = true;
			}
		}
		return $this->query('?action=move&format=json', $params);
	}
	/**
	 * Rollback an edit.
	 * @param string $title Title of page to rollback.
	 * @param string $user Username of last edit to the page to rollback.
	 * @param string|null $reason Edit summary to use for rollback.
	 * @param bool $bot mark the rollback as bot.
	 * @return array
	 */
	function rollback(string $title, string $user, string $reason = null, bool $bot = true): array{
		$ret =
			$this->query('?action=query&prop=revisions&rvtoken=rollback&titles=' . urlencode($title) . '&format=json');
		foreach($ret['query']['pages'] as $x){
			$token = $x['revisions'][0]['rollbacktoken'];
			break;
		}
		$params = [
			'title' => $title,
			'user' => $user,
			'token' => $token,
		];
		if($bot){
			$params['markbot'] = true;
		}
		if($reason != null){
			$params['summary'] = $reason;
		}
		return $this->query('?action=rollback&format=json', $params);
	}
	/**
	 * Blocks a user.
	 * @param string $user The user to block.
	 * @param string $reason The block reason.
	 * @param string $expiry The block expiry.
	 * @param string|null $options a piped string containing the block options.
	 * @param bool $retry
	 * @return array
	 */
	function block(string $user, string $reason = 'vand', string $expiry = 'infinite', string $options = null, bool $retry
	= true): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'expiry' => $expiry,
			'user' => $user,
			'reason' => $reason,
			'token' => $this->token,
		];
		if($options != null){
			$option = explode('|', $options);
			foreach($option as $o){
				$params[$o] = true;
			}
		}
		$ret = $this->query('?action=block&format=json', $params);
		/* Retry on a failed token. */
		if($retry and $ret['error']['code'] == 'badtoken'){
			$this->token = $this->getedittoken();
			return $this->block($user, $reason, $expiry, $options, false);
		}
		return $ret;
	}
	/**
	 * Unblocks a user.
	 * @param string $user The user to unblock.
	 * @param string $reason  The unblock reason.
	 * @return array
	 */
	function unblock(string $user, string $reason): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'user' => $user,
			'reason' => $reason,
			'token' => $this->token,
		];
		return $this->query('?action=unblock&format=json', $params);
	}
	/**
	 * Emails a user.
	 * @param string $target The user to email.
	 * @param string $subject The email subject.
	 * @param string $text The body of the email.
	 * @param bool $ccme Send a copy of the email to the user logged in.
	 * @return array
	 */
	function email(string $target, string $subject, string $text, bool $ccme): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'target' => $target,
			'subject' => $subject,
			'text' => $text,
			'token' => $this->token,
		];
		if($ccme){
			$params['ccme'] = true;
		}
		return $this->query('?action=emailuser&format=json', $params);
	}
	/**
	 * Deletes a page.
	 * @param string $title The page to delete.
	 * @param string $reason  The delete reason.
	 * @return array
	 */
	function delete(string $title, string $reason): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'title' => $title,
			'reason' => $reason,
			'token' => $this->token,
		];
		return $this->query('?action=delete&format=json', $params);
	}
	/**
	 * Undeletes a page.
	 * @param string $title The page to undelete.
	 * @param string $reason  The undeleted reason.
	 * @return array
	 */
	function undelete(string $title, string $reason): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'title' => $title,
			'reason' => $reason,
			'token' => $this->token,
		];
		return $this->query('?action=undelete&format=json', $params);
	}
	/**
	 * (Un)Protects a page.
	 * @param string $title The page to (un)protect.
	 * @param string $protections The protection levels (e.g. 'edit=autoconfirmed|move=sysop')
	 * @param string $expiry When the protection should expire (e.g. '1 day|infinite')
	 * @param string $reason The (un)protect reason.
	 * @param bool $cascade Enable cascading protection? (defaults to false)
	 * @return array
	 */
	function protect(string $title, string $protections, string $expiry, string $reason, bool $cascade): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'title' => $title,
			'protections' => $protections,
			'expiry' => $expiry,
			'reason' => $reason,
			'token' => $this->token,
		];
		if($cascade){
			$params['cascade'] = true;
		}
		return $this->query('?action=protect&format=json', $params);
	}
	/**
	 * Uploads an image.
	 * @param string $filename The destination file name.
	 * @param string $localFile The local file path.
	 * @param string $desc The upload discrption (defaults to '').
	 * @return array
	 */
	function upload(string $filename, string $localFile, string $desc = ''): array{
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$params = [
			'filename' => $filename,
			'comment' => $desc,
			'text' => $desc,
			'token' => $this->token,
			'ignorewarnings' => '1',
			'file' => '@' . $localFile,
		];
		return $this->query('?action=upload&format=json', $params);
	}

	/*
	$title - page
	$revs - rev ids to delete (seperated with ,)
	$comment - delete comment
	*/
	/**
	 * @param string $title
	 * @param $revs
	 * @param $comment
	 * @return bool|string
	 */
	function revdel(string $title, $revs, $comment){
		if($this->token == null){
			$this->token = $this->getedittoken();
		}
		$post = [
			'wpEditToken' => $this->token,
			'ids' => $revs,
			'target' => $title,
			'type' => 'revision',
			'wpHidePrimary' => 1,
			'wpHideComment' => 1,
			'wpHideUser' => 0,
			'wpRevDeleteReasonList' => 'other',
			'wpReason' => $comment,
			'wpSubmit' => 'Apply to selected revision(s)',
		];
		return $this->post(str_replace('api.php', 'index.php', $this->url) .
			'?title=Special:RevisionDelete&action=submit', $post);
	}
	/**
	 * Changes a users rights.
	 * @param string $user   The user we're working with.
	 * @param string $add    A pipe-separated list of groups you want to add.
	 * @param string $remove A pipe-separated list of groups you want to remove.
	 * @param string $reason The reason for the change (defaults to '').
	 * @return array
	 */
	function userrights(string $user, string $add, string $remove, string $reason = ''): array{
		// get the userrights token
		$token =
			$this->query('?action=query&list=users&ususers=' . urlencode($user) . '&ustoken=userrights&format=json');
		$token = $token['query']['users'][0]['userrightstoken'];
		$params = [
			'user' => $user,
			'token' => $token,
			'add' => $add,
			'remove' => $remove,
			'reason' => $reason,
		];
		return $this->query('?action=userrights&format=json', $params);
	}
	/**
	 * Gets the number of images matching a particular sha1 hash.
	 * @param string $hash The sha1 hash for an image.
	 * @return int
	 */
	function imagematches(string $hash): int{
		$x = $this->query('?action=query&list=allimages&format=json&aisha1=' . $hash);
		return count($x['query']['allimages']);
	}
	/**  BMcN 2012-09-16
	 * Retrieve a media file's actual location.
	 * @param string $title The "File:" page on the wiki which the URL of is desired.
	 * @return string The URL pointing directly to the media file (Eg http://upload.mediawiki
	 * .org/wikipedia/en/1/1/Example
	 * .jpg)
	 */
	function getfilelocation(string $title){
		$x = $this->query('?action=query&format=json&prop=imageinfo&titles=' . urlencode($title) .
			'&iilimit=1&iiprop=url');
		foreach($x['query']['pages'] as $ret){
			return $ret['imageinfo'][0]['url'] ?? false;
		}
		le("what?");throw new \LogicException();
	}
	/**  BMcN 2012-09-16
	 * Retrieve a media file's uploader.
	 * @param string $title The "File:" page
	 * @return bool|string The user who uploaded the topmost version of the file.
	 */
	function getfileuploader(string $title){
		$x = $this->query('?action=query&format=json&prop=imageinfo&titles=' . urlencode($title) .
			'&iilimit=1&iiprop=user');
		foreach($x['query']['pages'] as $ret){
			return $ret['imageinfo'][0]['user'] ?? false;
		}
		le("what?");throw new \LogicException();
	}
	/**
	 * Add a category to a page
	 * @param string $title page we're working with.
	 * @param string $category category that you want to add.
	 * @param string $summary summary to use.
	 * @param bool $minor or not to mark edit as minor.  (Default false)
	 * @param bool $bot or not to mark edit as a bot edit.  (Default true)
	 * @param array result
	 **/
	function addcategory( string $title, string $category, string $summary = '', bool $minor = false, bool $bot = true
	): array{
		$text = $this->getpage( $title );
		$text.= "\n[[Category:" . $category . "]]";
		return $this->edit($title, $text, $summary, $minor, $bot);
	}

	/**
	 * Find a string
	 * @param string $title page we're working with.
	 * @param string $string string that you want to find.
	 * @return int value (1 found and 0 not-found)
	 **/
	function findstring( string $title, string $string ): int{
		$text = $this->getpage( $title );
		if( strstr( $text, $string ) )
			return 1;
		else
			return 0;
	}

	/**
	 * Replace a string
	 * @param string $title page we're working with.
	 * @param string $string string that you want to replace.
	 * @param string $newstring string that will replace the present string.
	 * @return string the new text of page
	 **/
	function replacestring( string $title, string $string, string $newstring ): string{
		$text = $this->getpage( $title );
		return str_replace( $string, $newstring, $text );
	}

	/**
	 * Get a template from a page
	 * @param string $title page we're working with
	 * @param string $template name of the template we are looking for
	 * @return string|null the searched (NULL if the template has not been found)
	 **/
	function gettemplate( string $title, string $template ): ?string{
		$text = $this->getpage( $title );
		$template = preg_quote( $template, " " );
		$r = "/{{" . $template . "(?:[^{}]*(?:{{[^}]*}})?)+(?:[^}]*}})?/i";
		preg_match_all( $r, $text, $matches );
		return $matches[0][0] ?? null;
	}
	/**
	 * @param $url
	 * @return mixed
	 */
	private function getTime($url){
		$time = microtime(1);
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_USERAGENT, 'php wikibot classes');
		/* Crappy hack to add extra cookies, should be cleaned up */
		$cookies = null;
		foreach($this->cookie_jar as $name => $value){
			if(empty($cookies)) $cookies = "$name=$value"; else
				$cookies .= "; $name=$value";
		}
		if($cookies != null) curl_setopt($this->ch, CURLOPT_COOKIE, $cookies);
		return $time;
	}
	/**
	 * @return string[]
	 */
	function getAllPagesTitles(): array{
		$namespaces = range( 0, 15 ); // Default namespaces
		// Extra namespaces
		#$namespaces[] = 500;
		#$namespaces[] = 501;
		$namespaces = array_filter( $namespaces, function ( $var ): bool{
			return ( $var != 6 ); // Filter out the File: namespace
		});
		$this->login();
		$this->iterate ( $namespaces,); // Everything but File: namespace
		$this->iterate ( [6]); // Only the File: namespace
		return $this->pageTitles;
	}


	// Retrieve the data and store it in the file
	/**
	 * @param array $namespaces
	 */
	private function iterate (array $namespaces ) {
		foreach ( $namespaces as $namespace ) {
			$done = false;
			$apfrom = '';
			while ( !$done ) {
				$query = "?action=query&format=php&list=allpages&aplimit=500&apnamespace=$namespace";
				if ( $apfrom ) {
					$query .= "&apfrom=$apfrom";
				}
				$ret = $this->query ( $query );
				if ( !isset ( $ret['query-continue'] ) ) {
					$done = true;
				} else {
					$apfrom = $ret['query-continue']['allpages']['apfrom'];
				}
				foreach ( $ret['query']['allpages'] as $thisPage ) {
					$this->pageTitles[] = $thisPage['title'];
				}
			}
		}
	}
}
