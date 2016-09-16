# Database Trigger Samples

Below is an example of a database trigger function

## Inventory Images After Update

This function is part of a system that handled vehicle inventory data. There was a requirement where certain 
applications had to work with the database directly.  This meant that some of the basic record and model logic needed
to reside in the database as well. 

Another value that had to be pulled as part of a vehicle query was the number of images that were associated with that
vehicle. Images would be applied to a table with the vehicle foreign key. The trigger looks at the images assigned to a
vehicle and updates the count in the vehicle record.
