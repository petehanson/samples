# Database Function Samples

Below is a sampling of functions written for MySQL. 

## lowprice

This function is part of a system that handled vehicle inventory data. There was a requirement where certain 
applications had to work with the database directly.  This meant that some of the basic record and model logic needed
to reside in the database as well. 

One feature that was needed is a calculation of the lowest price of the vehicle. This function would compare a number
of fields and provide the lowest price. In a case where I had to build business logic into the database, through the
use of a function or procedure, I won't duplicate that functionality in the model layer. Instead, the PHP models that
used a vehicle record would also get the return value of this function call back as a property. 

## has_promotion

This function is part of a system that handled vehicle inventory data. There was a requirement where certain 
applications had to work with the database directly.  This meant that some of the basic record and model logic needed
to reside in the database as well. 

Another feature was to determine if there were specific promotions for a give vehicle. It's used as an indicator as
part of a vehicle record on the tool set that pulled the vehicle data. The function provided a boolean if that is the
case or not for the given vehicle.

