# PHP Test Samples

Below are a sampling of tests written for various projects. Tests written in PHPUnit and Codeception are included.

## FeedbackServiceTest.php

This is a test written to provide coverage over a feedback mechanism in a customer satisfaction tool that I built. The
 software is written in laravel and this is testing out a Laravel model. The test runs within the Laravel test runner,
 which uses PHPUnit.
 
## MessageTest.php

This is a test for an emailer library I wrote, that extends SwiftMailer, providing a tag search and replace feature, 
along with loading an email from a configuration file.  The test was written to run in Codeception. 

## StatementParserTest.php

This is a test that was written for the DBPatch application. It verifies that the SQL statement parser properly reads
the SQL statements from the patch files the application manages. The test was written to run in PHPUnit.

## ConfigLocationTest.php

This is a test that helps verify various configuration locations, that drive the DBPatch applicatoin. This test is 
writte in PHPUnit.