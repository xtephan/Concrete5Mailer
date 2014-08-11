Concrete5 Mailer Package
=========

Adds cool mailing functionality to Concrete5.

Version
----

1.0

Requirements
-----------

* Concrete5 5.6.2 to Concrete5 5.7

Installation
--------------

Download in Packages folder and install from dashboard.

Usage
--------------

Load the Mailer helper

    //Load the Mailer helper
    $mailer = Loader::helper('mailer','c5mailer');

Specify which template to use

    //Specify the Page name to be used as template
    $mailer->setMailTemplate( 'Demo Basic Template' );

    //Or: Use the collection ID, if known
    $mailer->setPageID( 139 );

    //Or: Use the collection
    $mailer->setPage( $page );

Optional: Set the sender. Fallback: the global defined one will be used

    $mailer->setSender( 'system@c5.com', 'Auto Robots' );
    $mailer->setSender( 'system@c5.com' ); //name is optional

Optional: Set reply to

    $mailer->setReplyTo( 'real-person@c5.com', 'Real Human' );
    $mailer->setReplyTo( 'real-person@c5.com' ); //name is optional

Required: Set the receiver

    $mailer->setReceiver( 'john@doe.com', 'John Doe' );
    $mailer->setReceiver( 'john@doe.com' ); //name is optional

Optional: set the subject. Fallback: the page description will be used

    $mailer->setSubject( 'Catchy Email Subject' );

    //email var can be used in subject as well
    $mailer->setSubject( 'Catchy Email Subject For %username%' );

Optional: add the replacements array

    $mailer->setReplacements(array(
            'username' => 'John Doe',
            'another_var' => 'Lorem Ipsum',
    ));

Optional: add attachments

    //By File Path
    $mailer->attachFileByPath( '/tmp/Comp_2.jpg' );
    $mailer->attachFileByPath( '/tmp/Comp_2.jpg', 'nice_name.jpg' ); //name is optional

    //And/Or Using a Concrete5 File
    $mailer->attachFile( File::getByID(3) );

    //And/Or Using a Concrete5 FileID
    $mailer->attachFileByID( 4 );

Send the email

    $mailer->send();

License
----

(C) Copyright Stefan Fodor(stefan@unserialized.dk) @ 2014

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.


Lastly
----
Software built with love in Denmark.