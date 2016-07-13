# Laravel Samples

Some introduction text for this section

## CloudFlareAPI.php
This is a model that I set up to make curl calls against the Cloudflare API to handle cache expires.  Part of the
solution on this project pushes a series of asset files, JavaScript files and JSON survey configs, to S3 buckets.
Those buckets are statically hosted and are cached through Cloudflare. When we do a deployment, we have to expire the
Cloudflare cache. This solution drastically reduced load serving the assets from EC2 instances, and later the costs
of delivering the files over CloudFront. 

This provides an interesting example of how I can front a REST API and provide a OOP based model for interaction with
the API.

About the application:  This sample comes from a client project that dealt heavy use of cached static assets served
to third party websites.  The client code served helps collect information from site visitors and centrally stores the
data for business intelligence reporting. 

## SurveyDataGateway.php
This is a model that showcases some operations for survey files that would interact with two different storage vehicles.
Originally the project used Elasticsearch to hold the survey-based data. Later, the project was refactored to store and
host survey files in JSON files on S3 and served through Cloudflare.

This gives another example of operating with third-party systems. This particular feature helps with some of the
automation around JSON file deployments and retrievals.

About the application:  This sample comes from a client project that dealt heavy use of cached static assets served
to third party websites.  The client code served helps collect information from site visitors and centrally stores the
data for business intelligence reporting. 

## DashboardController.php
This controller powers a dashboard that shows summary roll up data of customer satisfaction data and a customer list,
where various management operations can be executed from.

The main thing I like to point out with this sample is that the skinny controller/fat model design was used. The
controller is only used for handling the request, structuring that incoming data, and handing off the calls to models,
which handle the heavy lifting. Finally either views or JSON responses are sent back.

About the application: The toolset is used to collect customer feedback on a continuous basis. Based on a schedule, the 
application would email out to a defined list of customer contacts, asking them to provide a quick mood rating on how 
they feel about the client relationship. On a dashboard, overall metrics are computed and comment data is rolled up for review.
