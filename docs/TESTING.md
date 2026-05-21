# Testing Guide

## PHPUnit / Pest setup

```xml
<env name="MAIL_MAILER" value="mailbox"/>
<env name="MAILBOX_STORAGE_DRIVER" value="array"/>
<env name="MAILBOX_LOCAL_ONLY" value="false"/>
```

## Assertion API

```php
Mailbox::assertSent();
Mailbox::assertSentCount(2);
Mailbox::assertNothingSent();
Mailbox::assertSent(fn ($email) => $email->hasSubject('Invoice'));
```

## Pest expectations

```php
expectMailbox()->to('user@example.com')->withSubject('Welcome')->assert();
```
