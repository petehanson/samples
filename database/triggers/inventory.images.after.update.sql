drop trigger if exists content_table_INV_INVENTORY_IMAGE_after_update;
delimiter |
create trigger content_table_INV_INVENTORY_IMAGE_after_update after update on content_table_INV_INVENTORY_IMAGE for each row
begin

declare var_image_count int default 0;

select count(id) into var_image_count from content_table_INV_INVENTORY_IMAGE where inventory_id = new.inventory_id;

update content_table_INV_INVENTORY set imagecount = var_image_count where id = new.inventory_id;

end|
delimiter ;
