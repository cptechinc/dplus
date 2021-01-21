# Item Master cost update from VXM:

## Update if:
1. X-ref `VexrUnitCost` has changed
2. If the config `AptbConfVxmCostItemUpdM` (`ap_config`) from APC is ‘Y’
3. ITM Our Item Cost Base is Replacement (R) or Manual (M)
4. Vendor X-ref is a Primary (P) or Cost (C)
5. If there is **NOT** a Cost (C) X-ref for this Our Item
6. X-ref `VexrUnitCost` is **NOT** equal to `InitStanCost`
7. If `AptbConfVxmCostMMesg` = 'Y' **user** must confirm

## Update Procedures
1.  Read `inv_item_mast` for Our Item ID
2.  Verify Item Cost Base `InitStanCostBase` is Replacement (R) or Manual (M)
3.  Update `InitStanCost` from X-ref `VexrUnitCost`
4.  Update `InitStanCostLastDate` with today's date
10. Save `inv_item_mast`
