# Item Master price update from VXM:

## Update if:
1. If the Vendor List Price has changed
2. If the config AptbConfVxmListItemUpd (ap_config) from APC screen 6 is ‘Y’
2. Vendor X-ref is a Primary (P)


## Update Procedures
1.	Calculate the Update Item Base Price =  ((Vendor List Price / [Purch UOM conversion]) * [Item UOM conversion])

2. Create / Read `inv_item_price` record for Our Item ID

3. If `InprPricBase` is equal to the Update Item Base Price, **skip**

4. If `InprPricBase` is equal to zero, the Update Item Price Base is moved into `InprPricBase` and the current date is moved into `InprPricLastDate` and the Mysql record is saved
without  updating the price 1 – 10 fields

5. Calculate the Price Change Percent =  ([Update Item Base Price] – `InprPricBase`) / `InprPricBase`)

6. If `InprPricPric1` = zero, bypass the recalculation.  If not zero, recalculate `InprPricPric1` =  `InprPricPric1` + (`InprPricPric1` * Price Change Percent)

7. This same recalc will be done for prices 2 – 10.

8. Update  `InprPricBase`= [Update Item Base Price]
9. Update `InprPricLastDate` = [Current Date]
10. Save `inv_item_price`
