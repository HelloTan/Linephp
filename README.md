EvilBot
====


## DISCLAIMER

I do not claim the source code of LineCross to be mine. I just use & developed it.

Usage of EvilBot for kicking group targets without prior mutual consent is **inappropriate**. It is the end user's responsibility to obey all applicable local. Developers assume no liability and are not responsible for any misuse or damage caused by this bot chat.

## Description

A Simple Evil Bot on Line Messenger using Unofficial Library LineCross for PHP

## Requirements

I recommend you to use terminal from linux, ubuntu, or another linux distro 

 - PHP > 7.x
	 - php-curl
		```sudo apt-get install php-curl```
		
## Usage
1. Installation (Get AuthToken)
	
	- Use QR Code
	```php
	$Line = new LineCross();
	```
	- Use Command Prompt to run this file
	```
	php evilbot.php
	```
	- Get QR Code Link
		```line://au/q/xxxxx```
	- Open this url by using QR Code Generation
		http://www.qr-code-generator.com/
	- Scan the QR Code from your **Line Messenger on your phone**
	- Voila! You got AuthToken, and put this in
	```php
	const AUTH_TOKEN = "here";
	```
	- And AUTH_TOKEN variable to
	```php
	$AuthInfo = new \x9119x\AuthInfo(AUTH_TOKEN);
	```
	- And put $AuthInfo into
	```php
	 $Line = new LineCross($AuthInfo);
	```
	- **If you forget the AuthToken, it's stored in auth.txt**
	- Installation is complete.


2. Bot Usage
	
	- Evil Feature
		- Invite the bot into a group and wait untill the bot join to the group.
		- Type the command which is "kick" (lowercase) in group chat.
		- The bot start kicking peoples from the group
		- The bot **CANNOT** kick above 50 account a day.

	- Get Display Picture
		- Share contact that you want to get his/her Display Picture
		- Open the link "Picture URL"
		- And download it.
 

## Author

- <h4>LineCross</h4> [x9119x](https://twitter.com/_x9119x_)
- <h4>EvilBot</h4> [zombozo12](https://github.com/zombozo12)

