<?php
//000000000000s:616:"SELECT SUM(cope_amount) AS amount FROM 16860_oc_b2b_order obo WHERE ( obo.sc_code = '1010000000077' ) AND ( obo.uc_code = '1230000005948' ) AND (  (  ( obo.pay_type = 'ONLINE' ) AND ( obo.pay_status = 'PAY' ) AND (  (obo.create_time BETWEEN 1443628800 AND 1446307199 ) ) ) OR (  ( obo.ship_method = 'DELIVERY' ) AND ( obo.pay_type IN ('COD','TERM') ) AND ( obo.ship_status IN ('SHIPPED','TAKEOVER') ) AND (  (obo.ship_time BETWEEN 1443628800 AND 1446307199 ) ) ) OR (  ( obo.ship_method = 'PICKUP' ) AND ( obo.pay_type IN ('COD','TERM') ) AND (  (obo.takeover_time BETWEEN 1443628800 AND 1446307199 ) ) ) ) LIMIT 1  ";
?>