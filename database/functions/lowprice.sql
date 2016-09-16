DELIMITER :

DROP FUNCTION IF EXISTS low_price:
-- vbook_value is still being passed in but not being considered in the function any more
CREATE FUNCTION low_price(vmsrp DECIMAL(10,2), vspecials_price DECIMAL(10,2), vprice DECIMAL(10,2), vinternet_price DECIMAL(10,2), vselling_price DECIMAL(10,2), vbook_value DECIMAL(10,2), visspecial VARCHAR(255))

  RETURNS DECIMAL(10,2)
  BEGIN
	DECLARE max_dummy_value INT DEFAULT 0;
	
	SET vspecials_price = IF(visspecial = "True", vspecials_price, 0);
	set vmsrp = if(vmsrp is null, 0, vmsrp);
	set vspecials_price = if(vspecials_price is null, 0, vspecials_price);
	set vprice = if(vprice is null, 0, vprice);
	set vinternet_price = if(vinternet_price is null, 0, vinternet_price);
	set vselling_price = if(vselling_price is null, 0, vselling_price);
	set vbook_value = if(vbook_value is null, 0, vbook_value);
	
	SET max_dummy_value = GREATEST(vmsrp, vspecials_price, vprice, vinternet_price, vselling_price, vbook_value);
	IF max_dummy_value = 0 THEN
		RETURN -1;
	ELSE
		SET max_dummy_value = max_dummy_value+1;
		SET vmsrp = IF(vmsrp = 0, max_dummy_value, vmsrp);
		SET vspecials_price = IF(vspecials_price = 0 OR ISNULL(vspecials_price), max_dummy_value, vspecials_price);
		SET vprice = IF(vprice = 0 OR ISNULL(vprice), max_dummy_value, vprice);
		SET vinternet_price = IF(vinternet_price = 0 OR ISNULL(vinternet_price), max_dummy_value, vinternet_price);
		SET vselling_price = IF(vselling_price = 0 OR ISNULL(vselling_price), max_dummy_value, vselling_price);
		SET vbook_value = IF(vbook_value = 0 OR ISNULL(vbook_value), max_dummy_value, vbook_value);
		
		RETURN LEAST(vmsrp, vspecials_price, vprice, vinternet_price, vselling_price, vbook_value);
	END IF;
	
  END:
  
  DELIMITER ;
