<?php

namespace OnePilot\Client\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mail;
use OnePilot\Client\Exceptions\OnePilotException;
use OnePilot\Client\Middlewares\Authentication;

class MailTesterController extends Controller
{
    public function __construct()
    {
        $this->middleware(Authentication::class);
    }

    /**
     * @throws OnePilotException
     */
    public function send(Request $request)
    {
        if (empty($email = $request->email)) {
            throw new OnePilotException('Email parameter is missing', 400);
        }

        try {
            $this->sendEmail($email);
        } catch (Exception $e) {
            throw new OnePilotException('Error when sending email', 500, $e);
        }

        return [
            'message' => 'Sent',
        ];
    }

    /**
     * @param $email
     */
    protected function sendEmail($email)
    {
        Mail::send([], [], function (\Illuminate\Mail\Message $message) use ($email) {
            $message
                ->to($email)
                ->subject('Test mail from 1Pilot.io to ensure emails are properly sent');

            // Laravel < 9.x SwiftMailer
            if (method_exists($message, 'getSwiftMessage')) {
                $message->setBody($this->getBody());

                return;
            }

            // Laravel >= 9.x Symfony Mailer
            $message->text($this->getBody());
            $message->html($this->getBodyHtml());
        });
    }

    protected function getBody()
    {
        $siteUrl = config('app.url');

        return <<<EOF
This email was automatically sent by the 1Pilot Client installed on $siteUrl.

Ground control to Major Tom
Ground control to Major Tom
Take your protein pills and put your helmet on

Ground control to Major Tom
(10, 9, 8, 7)
Commencing countdown, engines on
(6, 5, 4, 3)
Check ignition, and may God's love be with you
(2, 1, liftoff)

This is ground control to Major Tom,

You've really made the grade
And the papers want to know whose shirts you wear
Now it's time to leave the capsule if you dare

This is Major Tom to ground control
I'm stepping through the door
And I'm floating in the most of peculiar way
And the stars look very different today

For here am I sitting in a tin can
Far above the world
Planet Earth is blue, and there's nothing I can do

Though I'm past 100,000 miles
I'm feeling very still
And I think my spaceship knows which way to go
Tell my wife I love her very much, she knows

Ground control to Major Tom,
Your circuit's dead, there's something wrong
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you...

Here am I floating round my tin can
Far above the moon
Planet Earth is blue, and there's nothing I can do...

Ground control to Major Tom,
Your circuit's dead, there's something wrong
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you hear me Major Tom?
Can you...

Space Oddity
David Bowie
EOF;
    }

    protected function getBodyHtml()
    {
        $siteUrl = config('app.url');

        return <<<EOF
<p>This email was automatically sent by the 1Pilot Client installed on $siteUrl.</p>

<p>Ground control to Major Tom<br>
Ground control to Major Tom<br>
Take your protein pills and put your helmet on</p>

<p>Ground control to Major Tom<br>
(10, 9, 8, 7)<br>
Commencing countdown, engines on<br>
(6, 5, 4, 3)<br>
Check ignition, and may God's love be with you<br>
(2, 1, liftoff)</p>

<p>This is ground control to Major Tom,</p>

<p>You've really made the grade<br>
And the papers want to know whose shirts you wear<br>
Now it's time to leave the capsule if you dare</p>

<p>This is Major Tom to ground control<br>
I'm stepping through the door<br>
And I'm floating in the most of peculiar way<br>
And the stars look very different today</p>

<p>For here am I sitting in a tin can<br>
Far above the world<br>
Planet Earth is blue, and there's nothing I can do</p>

<p>Though I'm past 100,000 miles<br>
I'm feeling very still<br>
And I think my spaceship knows which way to go<br>
Tell my wife I love her very much, she knows</p>

<p>Ground control to Major Tom,<br>
Your circuit's dead, there's something wrong<br>
Can you hear me Major Tom?<br>
Can you hear me Major Tom?<br>
Can you hear me Major Tom?<br>
Can you...</p>

<p>Here am I floating round my tin can<br>
Far above the moon<br>
Planet Earth is blue, and there's nothing I can do...</p>

<p>Ground control to Major Tom,<br>
Your circuit's dead, there's something wrong<br>
Can you hear me Major Tom?<br>
Can you hear me Major Tom?<br>
Can you hear me Major Tom?<br>
Can you...</p>

<p>Space Oddity<br>
David Bowie</p>
EOF;
    }
}
