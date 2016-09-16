DELIMITER :

DROP FUNCTION IF EXISTS has_promotion:

CREATE FUNCTION has_promotion(var_vin varchar(255))

  RETURNS Boolean
  BEGIN
	DECLARE var_promo_id INT DEFAULT 0;
	DECLARE var_has_promotion BOOLEAN Default false;

	select id into var_promo_id from content_table_INV_PROMOTION where curdate() between effectiveStartDate and effectiveEndDate and vin = var_vin limit 1;



	if var_promo_id > 0 then
		set var_has_promotion = true;
	else
		set var_has_promotion = false;
	end if;

	update content_table_INV_INVENTORY set has_promotion = var_has_promotion where vin = var_vin;
	return var_has_promotion;

  END:
  
  DELIMITER ;
