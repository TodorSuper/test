<?php
//000000000000s:612:"SELECT SUM(cope_amount) AS amount FROM 16860_oc_b2b_order obo WHERE ( obo.sc_code = 1010000000081 ) AND ( obo.uc_code = 1210000001046 ) AND (  (  ( obo.pay_type = 'ONLINE' ) AND ( obo.pay_status = 'PAY' ) AND (  (obo.create_time BETWEEN 1451577600 AND 1454255999 ) ) ) OR (  ( obo.ship_method = 'DELIVERY' ) AND ( obo.pay_type IN ('COD','TERM') ) AND ( obo.ship_status IN ('SHIPPED','TAKEOVER') ) AND (  (obo.ship_time BETWEEN 1451577600 AND 1454255999 ) ) ) OR (  ( obo.ship_method = 'PICKUP' ) AND ( obo.pay_type IN ('COD','TERM') ) AND (  (obo.takeover_time BETWEEN 1451577600 AND 1454255999 ) ) ) ) LIMIT 1  ";
?>