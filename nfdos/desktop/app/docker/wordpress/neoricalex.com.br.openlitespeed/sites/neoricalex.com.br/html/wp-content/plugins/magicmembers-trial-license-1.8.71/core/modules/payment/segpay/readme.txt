# Segpay Payment Gateway for Magic-member

## About
The Segpay Payment Gateway for Magic-member provides sellers with the option of saving A LOT of money on credit card merchant- and/or PayPal transaction-fees.

## Process in details
1. Create your Segpay account, if you do not have one already.
2. Generate EticketId, Username and Password.
3. Connect to FTP server of your website.
4. Navigate to wp-content/plugins/magicmembers/extend/modules/payment/ directory.
5. Copy the segpay folder inside payment directory.
6. Once files are copied, it will start showing payment gateway option in Magic member plugin settings area.
7. Go to Admin -> Magicmember -> paymet settings
	On payment gateways list, select the segpay option. A new tab will be added.	
	Enter the Segpay details (Your Segpay eclient ID, etc.)
	
	-Sample Data-
	Dynamic price E-ticket id      	191997:19535
	Live Payment Url:   	   		https://secure2.segpay.com/billing/poset.cgi
	Direct post - Price Hash Url:  	http://srs.segpay.com/PricingHash/PricingHash.svc/GetDynamicTrans
	Refund Url: 					http://srs.segpay.com/ADM.asmx/CancelMembership
	Dynamic Recurring-ticket id:   	191922:19526	
8. Segpay payment now ready for use.
9. To use static e-ticket feature, install our magicmembers-segpay-addon.
