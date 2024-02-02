<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection ForgottenDebugOutputInspection */
namespace Tests\SlimTests\Controllers;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\Measurement;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Exceptions\InvalidTimestampException;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\FoodsCommonVariables\BananasCommonVariable;
use App\Variables\QMUserVariable;
class DialogFlowTest extends \Tests\SlimTests\SlimTestCase
{
    public function testDialogFlowWelcomeAndNotificationTrack(){
        $this->deleteMeasurementsAndReminders();
        $this->setAuthenticatedUser(1);
        $this->assertEquals(0, Measurement::count());
        $cause = QMUserVariable::findOrCreateByNameOrId(1, BananasCommonVariable::NAME);
        $cause->createTrackingReminder();
	    $this->assertCount(1, TrackingReminder::all());
	    $notifications = TrackingReminderNotification::all();
		$times = $notifications->pluck(TrackingReminderNotification::FIELD_NOTIFY_AT)->toArray();
		QMLog::table($times, "Notification Times");
		foreach($times as $time){
			$this->assertDateLessThan(time(), $time);
		}
	    $this->assertCount(1, $notifications);
		$r = TrackingReminder::first();
		$this->assertNotNull($r->reminder_frequency);
		$u = $this->getUser();
	    $u2 = QMAuth::getQMUser();
		$this->assertEquals($u->id, $u2->id);
		$n = $u->getMostRecentPendingNotification();
		$this->assertNotNull($n);
        $r = $this->postAndGetDecodedBody('/api/v1/dialogflow', $this->getHiPostData());
        if(!$r){$this->assertNotNull($r, QMLog::print_r($r, true));}
		$msg = $r->fulfillmentMessages[1]->text->text[0];
        $this->assertStringContainsString("Did you have Bananas ", $msg, "We should have a banana reminder");
        $this->checkYesNoQuickReplies($r);
        $r = $this->postAndGetDecodedBody('/api/v1/dialogflow',
            $this->getTrackingReminderNotificationPostData($r->outputContexts));
        if(!$r){$this->assertNotNull($r, QMLog::print_r($r));}
        $measurements =  Measurement::count();
        if(!$measurements){
            $r = $this->postAndGetDecodedBody('/api/v1/dialogflow',
                $this->getTrackingReminderNotificationPostData($r->outputContexts));
        }
        $this->assertEquals(1, $measurements);
        $row = QMMeasurement::readonly()->first();
        $this->assertEquals(3, $row->value);
    }
    /**
     * @group api
     * @throws InvalidTimestampException
     */
    public function testYesNoNotificationTrack(){
        GlobalVariableRelationship::deleteAll();
        $this->createYesNoReminderAndNotifications();
        $this->assertEquals(0, Measurement::count());
        $r = $this->postAndGetDecodedBody('/api/v1/dialogflow', $this->getHiPostData());
        if(!$r){$this->assertNotNull($r, QMLog::print_r($r));}
        $this->assertContains("Did you have Hot Shower ", $r->fulfillmentMessages[1]->text->text[0]);
        $this->assertIsInt($r->outputContexts[0]->parameters->trackingReminderNotificationId);
        $q = $r->fulfillmentMessages[2]->quickReplies;
        $this->assertEquals("Yes", $q->quickReplies[0]);
        $this->assertEquals("No", $q->quickReplies[1]);
        $this->assertEquals("Snooze", $q->quickReplies[2]);
        $r = $this->postAndGetDecodedBody('/api/v1/dialogflow', $this->getYesPostData($r->outputContexts));
        if(!$r){$this->assertNotNull($r, QMLog::print_r($r));}
        $this->assertEquals(1, Measurement::count());
        $row = QMMeasurement::readonly()->first();
        $this->assertEquals(1, $row->value);
    }
    /**
     * @param $outputContexts
     * @return string
     */
    private function getYesPostData($outputContexts): string{
        return '{
            "responseId": "c54815eb-2a95-4204-85d9-a50676936692",
            "queryResult": {
              "queryText": "actions_intent_OPTION",
              "action": "tracking_reminder_notification",
              "parameters": {
                "notificationAction": "track",
                "value": "",
                "yesNo": ""
              },
              "allRequiredParamsPresent": true,
              "fulfillmentMessages": [
                {
                  "text": {
                    "text": [
                      ""
                    ]
                  }
                }
              ],
            "outputContexts": '.json_encode($outputContexts).',
              "intent": {
                "name": "projects/dr-modo/agent/intents/921bbe0e-6f16-490c-b243-1743081bb25d",
                "displayName": "Tracking Reminder Notification Intent"
              },
              "intentDetectionConfidence": 1,
              "languageCode": "en-us"
            },
            "originalDetectIntentRequest": {
              "source": "google",
              "version": "2",
              "payload": {
                "isInSandbox": true,
                "surface": {
                  "capabilities": [
                    {
                      "name": "actions.capability.MEDIA_RESPONSE_AUDIO"
                    },
                    {
                      "name": "actions.capability.AUDIO_OUTPUT"
                    }
                  ]
                },
                "inputs": [
                  {
                    "rawInputs": [
                      {
                        "query": "yes",
                        "inputType": "VOICE"
                      }
                    ],
                    "arguments": [
                      {
                        "textValue": "yes-button",
                        "name": "OPTION"
                      }
                    ],
                    "intent": "actions.intent.OPTION"
                  }
                ],
                "user": {
                  "lastSeen": "2018-08-18T06:00:04Z",
                  "idToken": "test-id-token",
                  "locale": "en-US",
                  "userId": "test-user-id"
                },
                "conversation": {
                  "conversationId": "1534658896419",
                  "type": "ACTIVE",
                  "conversationToken": "[\"tracking_reminder_notification\"]"
                },
                "availableSurfaces": [
                  {
                    "capabilities": [
                      {
                        "name": "actions.capability.WEB_BROWSER"
                      },
                      {
                        "name": "actions.capability.SCREEN_OUTPUT"
                      },
                      {
                        "name": "actions.capability.AUDIO_OUTPUT"
                      }
                    ]
                  }
                ]
              }
            },
            "session": "projects/dr-modo/agent/sessions/1534658896419"
          }
        ';
    }
    public function testDialogFlowWelcomeAndNotificationSkip(){
        $this->deleteMeasurementsAndReminders();
        //QMUser::updateById(1, [\App\Models\User::FIELD_USER_EMAIL => 'm@thinkbynumbers.org']);
        $this->assertEquals(0, Measurement::count());
        $cause = QMUserVariable::findOrCreateByNameOrId(1, BananasCommonVariable::NAME);
        $cause->createTrackingReminder();
	    $this->assertCount(1, TrackingReminder::all());
	    $this->assertCount(1, TrackingReminderNotification::all());
        $this->setAuthenticatedUser(1);
        $response = $this->postAndGetDecodedBody('/api/v1/dialogflow', $this->getHiPostData());
        if(!$response){$this->assertNotNull($response, QMLog::print_r($response));}
        $this->assertNotFalse(stripos($response->fulfillmentMessages[1]->text->text[0],
            "Did you have Bananas "),
            $response->fulfillmentMessages[1]->text->text[0]);
        $this->checkYesNoQuickReplies($response);
	    $this->setAuthenticatedUser(1);
        $response = $this->postAndGetDecodedBody('/api/v1/dialogflow', $this->getSkipPostData($response->outputContexts));
        if(!$response){$this->assertNotNull($response, QMLog::print_r($response));}
        $this->assertEquals(0, Measurement::count());
        $this->assertEquals(0, QMTrackingReminderNotification::readonly()
            ->whereNull(QMTrackingReminderNotification::FIELD_DELETED_AT)->count());
    }
    public function testGoogleAssistantMoodRatingAndReminderCreation(){
		Measurement::truncate();
        $this->assertEquals(0, Measurement::count());
        $this->setAuthenticatedUser(1);
        $data = $this->getMoodRecordingPostData();
	    $this->setAuthenticatedUser(1);
        $r = $this->postAndGetDecodedBody('/api/v1/dialogflow', $data);
        if(!$r){$this->assertNotNull($r, QMLog::print_r($r));}
        $this->assertEquals(1, Measurement::count());
        $row = Measurement::first();
        $this->assertEquals(3, $row->value);
        $this->assertContains( "I've recorded 3 out of 5 Overall Mood." , $r->fulfillmentMessages[0]->text->text[0]);
        $this->assertEquals( " Aside from any you've already added, what is an unpleasant emotion like ".
            "guiltiness, irritability, or nervousness you experience regularly? If you don't have any more to add, ".
            "just say Done with Emotions.   " ,
            $r->fulfillmentMessages[1]->text->text[0]);
	    $this->setAuthenticatedUser(1);
        $r = $this->postAndGetDecodedBody('/api/v1/dialogflow', $this->getAddMoodReminderPostData());
        $this->assertEquals(" OK. I'll ask you about your Overall Mood once a day.  ",
            $r->fulfillmentMessages[0]->text->text[0], $r->fulfillmentMessages[0]->text->text[0]);
        $msg = $r->fulfillmentMessages[1]->text->text[0];
	    $this->assertContains("How was your overall mood ", $msg);
	    $this->assertContains(" on a scale of 1 to 5?", $msg);
        $q = $r->fulfillmentMessages[2]->quickReplies;
        $v = QMUserVariable::getByNameOrId(1, OverallMoodCommonVariable::NAME);
        $lastValues = $v->getLastValuesInUserUnit();
        $this->assertEquals(3, $lastValues[0]);
		$this->assertArrayEquals(array (
			                         0 => 3,
			                         1 => 5,
			                         2 => 1,
			                         3 => 4,
			                         4 => 2,
		                         ), $q->quickReplies,
			"The last value recorded was 3 so that should be the first option. ".
			"We should be able to rate 1 to 5 in quick replies");
    }
    /**
     * @param $outputContexts
     * @return string
     */
    /**
     * @param $outputContexts
     * @return string
     */
    private function getSkipPostData($outputContexts): string{
        return '{
            "responseId": "a30e0ad3-e4e8-41f8-a949-73659c387125",
              "queryResult": {
                "queryText": "actions_intent_OPTION",
                "action": "tracking_reminder_notification",
                "parameters": {
                  "notificationAction": "track",
                  "value": "",
                  "yesNo": ""
                },
                "allRequiredParamsPresent": true,
                "fulfillmentMessages": [
                  {
                    "text": {
                      "text": [
                        ""
                      ]
                    }
                  }
                ],
                "outputContexts": '.json_encode($outputContexts).',
                "intent": {
                  "name": "projects/dr-modo/agent/intents/921bbe0e-6f16-490c-b243-1743081bb25d",
                  "displayName": "Tracking Reminder Notification Intent"
                },
                "intentDetectionConfidence": 1,
                "languageCode": "en-us"
              },
              "originalDetectIntentRequest": {
                "source": "google",
                "version": "2",
                "payload": {
                  "isInSandbox": true,
                  "surface": {
                    "capabilities": [
                      {
                        "name": "actions.capability.AUDIO_OUTPUT"
                      },
                      {
                        "name": "actions.capability.SCREEN_OUTPUT"
                      },
                      {
                        "name": "actions.capability.MEDIA_RESPONSE_AUDIO"
                      },
                      {
                        "name": "actions.capability.WEB_BROWSER"
                      }
                    ]
                  },
                  "inputs": [
                    {
                      "rawInputs": [
                        {
                          "query": "Skip"
                        }
                      ],
                      "arguments": [
                        {
                          "textValue": "skip-button",
                          "name": "OPTION"
                        }
                      ],
                      "intent": "actions.intent.OPTION"
                    }
                  ],
                  "user": {
                    "lastSeen": "2018-08-13T22:22:24Z",
                    "idToken": "test-token",
                    "locale": "en-US",
                    "userId": "test-user-id"
                  },
                  "conversation": {
                    "conversationId": "1534201552833",
                    "type": "ACTIVE",
                    "conversationToken": "[\"tracking_reminder_notification\"]"
                  },
                  "availableSurfaces": [
                    {
                      "capabilities": [
                        {
                          "name": "actions.capability.AUDIO_OUTPUT"
                        },
                        {
                          "name": "actions.capability.SCREEN_OUTPUT"
                        },
                        {
                          "name": "actions.capability.WEB_BROWSER"
                        }
                      ]
                    }
                  ]
                }
              },
              "session": "projects/dr-modo/agent/sessions/1534201552833"
            }';
    }
    /**
     * @return string
     */
    private function getHiPostData(): string{
        return '{
            "responseId": "5e296221-0ecd-4a2d-96e7-9155317a97fe",
            "queryResult": {
              "queryText": "hi",
              "action": "input.welcome",
              "parameters": {},
              "allRequiredParamsPresent": true,
              "fulfillmentText": "Oh. It\'s you. What do you want?",
              "fulfillmentMessages": [
                {
                  "text": {
                    "text": [
                      "Oh. It\'s you. What do you want?"
                    ]
                  }
                }
              ],
              "outputContexts": [],
              "intent": {
                "name": "projects/dr-modo/agent/intents/b69ed140-5dd7-4cf1-a5b7-f11f8d38bff0",
                "displayName": "Default Welcome Intent"
              },
              "intentDetectionConfidence": 1,
              "languageCode": "en"
            },
            "originalDetectIntentRequest": {
              "payload": {}
            },
            "session": "projects/dr-modo/agent/sessions/a2c3431e-133a-0279-ef66-730f152a5898"
          }';
    }
    /**
     * @param object $outputContexts
     * @return string
     */
    private function getTrackingReminderNotificationPostData($outputContexts): string{
        return '{
          "responseId": "8e3b5146-d32f-4353-985c-f0875b6020ff",
          "queryResult": {
            "queryText": "3",
            "action": "tracking_reminder_notification",
            "parameters": {
              "notificationAction": "track",
              "value": 3
            },
            "allRequiredParamsPresent": true,
            "fulfillmentMessages": [
              {
                "text": {
                  "text": [
                    ""
                  ]
                }
              }
            ],
            "outputContexts": '.json_encode($outputContexts).',
            "intent": {
              "name": "projects/dr-modo/agent/intents/921bbe0e-6f16-490c-b243-1743081bb25d",
              "displayName": "Tracking Reminder Notification Intent"
            },
            "intentDetectionConfidence": 1,
            "languageCode": "en"
          },
          "originalDetectIntentRequest": {
            "payload": {}
          },
          "session": "projects/dr-modo/agent/sessions/a2c3431e-133a-0279-ef66-730f152a5898"
        }
        ';
    }
    /**
     * @return string
     */
    private function getMoodRecordingPostData(): string{
        return '{
            "responseId": "51d77393-35ed-4254-9894-76c7ad74114c",
            "queryResult": {
              "queryText": "record 3 overall mood",
              "action": "measurment.record",
              "parameters": {
                "recordMeasurementTriggerPhrase": "record",
                "value": 3,
                "variableName": "Overall Mood",
                "unitName": "",
                "unitAbbreviatedName": ""
              },
              "allRequiredParamsPresent": true,
              "fulfillmentMessages": [
                {
                  "text": {
                    "text": [
                      ""
                    ]
                  }
                }
              ],
              "intent": {
                "name": "projects/dr-modo/agent/intents/63660c17-8146-48b5-847e-2d73ffbee270",
                "displayName": "Record Measurement Intent"
              },
              "intentDetectionConfidence": 1,
              "languageCode": "en"
            },
            "originalDetectIntentRequest": {
              "payload": {}
            },
            "session": "projects/dr-modo/agent/sessions/a2c3431e-133a-0279-ef66-730f152a5898"
          }
        ';
    }
    /**
     * @return string
     */
    private function getAddMoodReminderPostData(): string{
        return '{
            "responseId": "76557124-e36c-4143-a1b4-272639455072",
            "queryResult": {
              "queryText": "add mood",
              "action": "create_reminder",
              "parameters": {
                "variableName": "Overall Mood",
                "triggerPhrase": "add"
              },
              "allRequiredParamsPresent": true,
              "fulfillmentMessages": [
                {
                  "text": {
                    "text": [
                      ""
                    ]
                  }
                }
              ],
              "outputContexts": [
                {
                  "name": "projects/dr-modo/agent/sessions/a2c3431e-133a-0279-ef66-730f152a5898/contexts/create_reminder",
                  "lifespanCount": 2,
                  "parameters": {
                    "variableName": "Overall Mood",
                    "triggerPhrase.original": "add",
                    "variableName.original": "mood",
                    "triggerPhrase": "add"
                  }
                },
                {
                  "name": "projects/dr-modo/agent/sessions/a2c3431e-133a-0279-ef66-730f152a5898/contexts/tracking_reminder_notification",
                  "parameters": {
                    "variableName": "Overall Mood",
                    "unitName": "1 to 5 Rating",
                    "trackingReminderNotificationId": 29717774,
                    "triggerPhrase.original": "add",
                    "variableName.original": "mood",
                    "triggerPhrase": "add"
                  }
                }
              ],
              "intent": {
                "name": "projects/dr-modo/agent/intents/704c4e57-8032-47b5-8787-a5359bd9d76a",
                "displayName": "Create Reminder Intent"
              },
              "intentDetectionConfidence": 1,
              "languageCode": "en"
            },
            "originalDetectIntentRequest": {
              "payload": {}
            },
            "session": "projects/dr-modo/agent/sessions/a2c3431e-133a-0279-ef66-730f152a5898"
          }
        ';
    }
    /**
     * @param $response
     */
    protected function checkYesNoQuickReplies($response): void{
        $this->assertIsInt($response->outputContexts[0]->parameters->trackingReminderNotificationId);
        $q = $response->fulfillmentMessages[2]->quickReplies;
        $this->assertEquals(1, $q->quickReplies[0]);
        $this->assertEquals(0, $q->quickReplies[1]);
        $this->assertEquals("Snooze", $q->quickReplies[2]);
    }
}
