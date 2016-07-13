# PHP Test Samples

Below are a sampling of tests written for various projects. Tests written in PHPUnit and Codeception are included.

## FeedbackServiceTest.php

This is a test written to provide coverage over a feedback mechanism in a customer satisfaction tool that I built. The 
software is written in laravel and this is testing out a Laravel model. The test runs within the Laravel test runner, 
which uses PHPUnit.

About the application: The toolset is used to collect customer feedback on a continuous basis. Based on a schedule, the 
application would email out to a defined list of customer contacts, asking them to provide a quick mood rating on how 
they feel about the client relationship. On a dashboard, overall metrics are computed and comment data is rolled up for review.
 
## MessageTest.php

This is a test for an emailer library I wrote, that extends SwiftMailer, providing a tag search and replace feature, 
along with loading an email from a configuration file.  The test was written to run in Codeception. 

About the application: This is an extension to SwiftMailer that I find solves the meta data problem of sending most
emails.  In many MVC frameworks, you use a view to define your message, but things like who gets the email, who it's 
from, the subject, and attachments are typically defined right at the point of definition in the model or a library.
This tool helps centralize all those aspects into configuration files that can be loaded quickly to generate the email
that needs to be sent at that particular part of the workflow of the application. Along with a configuration centric 
approach, the tool also uses tag/variable replacement sequence, so you can replace values in any part of the massage. 
The tag engine uses Mustache, so any valid usage of Mustache works in the email templates. 

## StatementParserTest.php

This is a test that was written for the DBPatch application. It verifies that the SQL statement parser properly reads
the SQL statements from the patch files the application manages. The test was written to run in PHPUnit.

About the application:  DBPatch has been a tool that I wrote long ago and has been through a few different iterations. 
The current version provides a framework agnostic way of handling database patching. The core idea is that you track 
all the schema and data changes you need to make to a project's database, right along with the version of the code that
uses that modified schema. You update your project, see there are new patch files, and then apply the patches to keep
your database in sync.  This isn't a new concept and most modern frameworks now provide this capability.  This tool is
for projects that don't leverage one of those frameworks. 

## ConfigLocationTest.php

This is a test that helps verify various configuration locations, that drive the DBPatch application. This test is 
written in PHPUnit.

About the application:  DBPatch has been a tool that I wrote long ago and has been through a few different iterations. 
The current version provides a framework agnostic way of handling database patching. The core idea is that you track 
all the schema and data changes you need to make to a project's database, right along with the version of the code that
uses that modified schema. You update your project, see there are new patch files, and then apply the patches to keep
your database in sync.  This isn't a new concept and most modern frameworks now provide this capability.  This tool is
for projects that don't leverage one of those frameworks. 
