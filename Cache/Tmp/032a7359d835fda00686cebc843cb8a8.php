<?php
//000000000000s:600:"SELECT sum(cope_amount) as amount,count(*) as orders FROM `16860_oc_b2b_order` WHERE ( `sc_code` = '1010000000138' ) AND ( `uc_code` = '1210000002813' ) AND (  (  ( `pay_type` = 'ONLINE' ) AND ( `pay_status` = 'PAY' ) AND (  (`create_time` BETWEEN 1422720000 AND 1425139199 ) ) ) OR (  ( `ship_method` = 'DELIVERY' ) AND ( `pay_type` IN ('COD','TERM') ) AND ( `ship_status` IN ('SHIPPED','TAKEOVER') ) AND (  (`ship_time` BETWEEN 1422720000 AND 1425139199 ) ) ) OR (  ( `ship_method` = 'PICKUP' ) AND ( `pay_type` IN ('COD','TERM') ) AND (  (`takeover_time` BETWEEN 1422720000 AND 1425139199 ) ) ) ) ";
?>