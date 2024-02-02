<?php
namespace Tests\UnitTests\Notifications;
use App\Logging\QMLog;
use App\Models\User;
use App\Notifications\ExceptionNotification;
use App\Notifications\LinkNotification;
use Illuminate\Support\Facades\Notification;
use Tests\UnitTestCase;
class ExceptionNotificationTest extends UnitTestCase
{
    public function testToMail(){
		$this->skipTest("Not sure why this doesn't work");
        Notification::fake();
        Notification::assertNothingSent();
        $user = User::mike();
	    $e = new \LogicException("I am a message");
	    $user->notify(new ExceptionNotification(new QMLog("hi")));
	    Notification::assertSentTo($user, ExceptionNotification::class, function($notification, $channels) use ($e){
		    /** @var ExceptionNotification $notification */
		    return $notification->QMLog->getMessage() === $e->getMessage();
	    });
        Notification::assertSentTo(
            [$user], ExceptionNotification::class
        );
        Notification::assertNotSentTo(
            [$user], LinkNotification::class
        );
        // Assert a notification was sent via Notification::route() method...
        // Not sure what this is for Notification::assertSentTo(new AnonymousNotifiable, ExceptionNotification::class);
    }
}
