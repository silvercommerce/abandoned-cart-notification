<% include ilateral\SilverStripe\Notifier\Includes\EmailHead %>

<p>$Content</p>

<p><strong>$Link</strong></p>

<% if $Object.Items.exists %>
    <hr/>

    <table style="width: 100%;">
        <thead>
            <tr>
                <th style="text-align: left"><%t Orders.Details "Details" %></th>
                <th style="text-align: right"><%t Orders.QTY "Qty" %></th>
                <th style="text-align: right"><%t Orders.Price "Price" %></th>
            </tr>
        </thead>

        <tbody><% loop $Object.Items %>
            <tr>
                <td>
                    {$Title} <% if $StockID %>($StockID)<% end_if %><br/>
                    <em>$CustomisationHTML</em>
                </td>
                <td style="text-align: right">
                    {$Quantity}
                </td>
                <td style="text-align: right">
                    {$getFormattedPrice($ShowPriceWithTax)}
                </td>
            </tr>
        <% end_loop %></tbody>
    </table>

    <hr/>
<% end_if %>

<p>
    <%t Orders.CustomerEmailFooter 'Many thanks' %>,<br/><br/>
    {$SiteConfig.Title}
</p>

<% include ilateral\SilverStripe\Notifier\Includes\EmailFoot %>
