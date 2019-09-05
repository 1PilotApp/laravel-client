<p align="center">
  <a href="https://1pilot.io/laravel"><img src="https://1pilot.io/assets/images/repos/1pilot_logo_laravel6.png" alt="1Pilot.io - a universal dashboard to effortlessly manage all your sites"></a>
</p>

<p align="center">
<a href="https://packagist.org/packages/1pilotapp/laravel-client"><img alt="Latest Version on Packagist" src="https://img.shields.io/packagist/v/1PilotApp/laravel-client.svg?style=flat-square"></a>
<a href="/1PilotApp/laravel-client/blob/master/LICENSE.md"><img alt="Software License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square"></a>
<a href="https://travis-ci.org/1PilotApp/laravel-client"><img alt="Build Status" src="https://img.shields.io/travis/1PilotApp/laravel-client/master.svg?style=flat-square"></a>
<a href="https://packagist.org/packages/1PilotApp/laravel-client"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/1pilotapp/laravel-client.svg?style=flat-square"></a>
</p>

<p align="center">
  <a href="https://1pilot.io/laravel">Website</a>
  <span> · </span>
  <a href="https://app.1pilot.io/register">Free Trial</a>
  <span> · </span>
  <a href="https://1pilot.io/#pricing">Pricing</a>
  <span> · </span>
  <a href="https://docs.1pilot.io/setup/laravel" target="_blank" >Documentation</a>
  <span> · </span>
  <a href="https://docs.1pilot.io/api/introduction" target="_blank">API</a>
  <span> · </span>
  <a href="https://1pilot.nolt.io" target="_blank">Feedback</a>
  <span> · </span>
  <a href="mailto:support@1pilot.io" target="_blank">Support</a>
</h4><br>

<blockquote>
<p><strong>[You]</strong> What are you, strange being?</p>
<p><strong>[1Pilot]</strong> Greetings, traveller. I am 1Pilot. I am a tool created to monitor and manage earthbound Laravel applications.</p>
<p><strong>[You]</strong> OK... What is your primary purpose?</p>
<p><strong>[1Pilot]</strong> I was created to reduce the time you spend managing applications, so that you can focus on your primary mission.</p>
<p><strong>[You]</strong> Wait, what? How could you know... What is my primary mission?</p>
<p><strong>[1Pilot]</strong> My records indicate that your primary mission is ...searching database... to code in your underwear, jamming to Radiohead.</p>
<p><strong>[You]</strong> 😲... 🤪... Sweet.</p>
<p><strong>[1Pilot]</strong> Affirmative, traveller. ‘Sweet’.</p>
</blockquote>

... _Initiating introduction protocol_ ...  

## Control all your applications from a single, powerful application monitoring tool.

- **Uptime monitoring** - Get instant notifications about downtime and fix it before everyone else even knows it’s an issue.  

- **SSL certificate monitoring** - Keep track of certificates across all your applications and set reminders of their expiration dates.  
- **Config file and server version monitoring** - Be alerted when a config file is edited or when PHP, Database or WEB servers are updated.  

- **Composer package management** - See installed composer packages across all your applications and track their updates. Know exactly when new versions are available and log a central history of all changes.  
  
- **Robust notification system** - Get instant notifications across email, Slack and Discord. Too much? Then create fully customisable alerts and summaries for each function and comms channel at a frequency that suits you.  

- **Full-featured 15-day trial** - Then $2/site/month with volume discounts available. No setup fees. No long-term contracts.  

... _Introduction protocol complete_ ...

You have just discovered our advanced monitoring tool for your Laravel applications and all the individual sites that you manage. We have designed it as a central dashboard to harmonise the maintenance of your entire website roster. Because we believe that coders should be out there coding. Let computers monitor computers, so that we humans don’t have to worry about it.

We searched the galaxy for a robust answer to our challenges, and found none. So, our team embarked on our greatest mission yet and 1Pilot was born.

<a href="https://app.1pilot.io/register">Try it for free</a> without any limitations for 15 days. No credit card required.

<a href="https://app.1pilot.io/register"><img src="https://user-images.githubusercontent.com/12028540/52998798-76510900-3424-11e9-9ad1-450ffc32f06a.jpg" alt="Get your first site onboard in under 3 minutes! Start the 15-day full-feature trial"></a>

## Install

``` bash
composer require 1pilotapp/laravel-client
```

You need to publish the config and set `private_key` to a random string
```
php artisan vendor:publish --provider="OnePilot\Client\ClientServiceProvider"
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email support@1pilot.io instead of using the issue tracker.

## Credits

- [1Pilot.io](https://github.com/1PilotApp)
- [All Contributors](https://github.com/1PilotApp/laravel-client/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
