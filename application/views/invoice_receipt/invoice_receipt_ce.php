<script type="text/javascript" src="<?php echo base_url() ?>jqwidgets/globalization/globalize.js"></script>
<script>
$(document).ready(function(){
    $("#delivery-date").jqxDateTimeInput({width: '250px', height: '25px', readonly: true <?php if(isset($is_view)){ echo ',disabled: true';} ?>}); 
    
    $("#clear-delivery-date").click(function(){
        <?php 
        if(!isset($is_view))
        {?>
        $("#delivery-date").val(null);
        <?php
        }
        ?>
    });
    
    <?php 
    if(isset($is_edit))
    {?>
    $("#delivery-date").jqxDateTimeInput('val', <?php echo "'" . date( 'd/m/Y' , strtotime($data_edit[0]['payment_date'])) . "'" ?>);   
    <?php 
    }
    ?>

    //=================================================================================
    //
    //   Unit Measure Data
    //
    //=================================================================================
    
    var url_unit = "<?php echo base_url() ;?>unit_measure/get_unit_measure_list"
    var unitSource =
    {
         datatype: "json",
         datafields: [
             { name: 'id_unit_measure'},
             { name: 'name'}
         ],
        id: 'id_unit_measure',
        url: url_unit ,
        root: 'data'
    };
    
    var unitAdapter = new $.jqx.dataAdapter(unitSource, {
        autoBind: true
    });
    
    //=================================================================================
    //
    //   PO Select
    //
    //=================================================================================
    
    var urlInvoice = "<?php echo base_url() ;?>invoice/get_invoice_open_list";
    var sourceInvoice =
    {
        datatype: "json",
        datafields:
        [
            { name: 'id_invoice'},
            { name: 'invoice_number'},
            { name: 'customer'},
            { name: 'customer_name'},
            { name: 'invoice_date'},
            { name: 'sub_total'},
            { name: 'ppn'},
            { name: 'total_invoice'}
        ],
        id: 'id_invoice',
        url: urlInvoice ,
        root: 'data'
    };
    var dataAdapterInvoice = new $.jqx.dataAdapter(sourceInvoice);
    
    
    $("#invoice-no").jqxInput({ source: dataAdapterInvoice, displayMember: "invoice_number", valueMember: "id_invoice", height: 23});
    
    $("#invoice-no").jqxInput({disabled: true});
    
    $("#select-invoice-popup").jqxWindow({
        width: 600, height: 500, resizable: false,  isModal: true, autoOpen: false, cancelButton: $("#Cancel"), modalOpacity: 0.01           
    });
    
    $("#select-invoice-grid").jqxGrid(
    {
        theme: $("#theme").val(),
        width: '100%',
        height: 400,
        selectionmode : 'singlerow',
        source: dataAdapterInvoice,
        columnsresize: true,
        autoshowloadelement: false,                                                                                
        sortable: true,
        filterable: true,
        showfilterrow: true,
        autoshowfiltericon: true,
        columns: [
            { text: 'Invoice No.', dataField: 'invoice_number', width: 150},
            { text: 'Customer', dataField: 'customer_name'},
            { text: 'Date', dataField: 'invoice_date', width: 150},
			
        ]
    });
    
    $("#invoice-select").click(function(){
        <?php 
        if(!isset($is_view))
        {?>
        $("#select-invoice-popup").jqxWindow('open');
        <?php
        }
        ?>
    });
    
    $('#select-invoice-grid').on('rowdoubleclick', function (event) 
    {
        <?php 
        if(!isset($is_edit))
        {?>
        var args = event.args;
        var data = $('#select-invoice-grid').jqxGrid('getrowdata', args.rowindex);
        $('#invoice-no').jqxInput('val', {label: data.invoice_number, value: data.id_invoice});
        var url = "<?php echo base_url()?>invoice/get_detail_invoice?id=" + data.id_invoice;
        var source =
        {
            datatype: "json",
            datafields:
            [
				{ name: 'id' },
                { name: 'id_product'},
                { name: 'product_category'},
                { name: 'merk'},
                { name: 'product_code'},
                { name: 'product_name'},
                { name: 'name'},
                { name: 'unit_name', value: 'unit', values: { source: unitAdapter.records, value: 'id_unit_measure', name: 'name' } },
                { name: 'unit'},            
                { name: 'category_name'},
                { name: 'qty', type: 'number'},
                { name: 'unit_price', type: 'number'},
                { name: 'total_price', type: 'number'}
            ],
            id: 'id',
            url: url ,
            root: 'data'
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#invoice-product-grid").jqxGrid({source: dataAdapter});
        $("#select-invoice-popup").jqxWindow('close');
        $("#subtotal").jqxNumberInput('val', data.sub_total);
        $("#tax").jqxNumberInput('val', data.ppn);
        $("#total").jqxNumberInput('val', data.total_invoice);
        
        var urlHistory = "<?php echo base_url() ?>invoice_receipt/get_invoice_receipt_history?id_invoice=" + data.id_invoice ;
        var sourceHistory =
        {
            datatype: "json",
            datafields:
            [
                { name: 'id_invoice_receipt'},
                { name: 'payment_date', type: 'date'},
                { name: 'total_payment'},
                { name: 'invoice_receipt_number'}
            ],
            id: 'id_invoice_receipt',
            url: urlHistory ,
            root: 'data'
        };
        var dataAdapterHistory = new $.jqx.dataAdapter(sourceHistory);
        $("#payment-history-grid").jqxGrid({source: dataAdapterHistory});
        <?php    
        }
        ?>
        
        get_payment_left(data.id_invoice, null);
        
    });
    
    <?php 
    if(isset($from_invoice) && $from_invoice == 'true')
    {?>
    
        $('#invoice-no').jqxInput('val', {label: '<?php echo $invoice[0]['invoice_number'] ?>', value: '<?php echo $invoice[0]['id_invoice'] ?>'});
    
    <?php    
    }
    ?>
    
    <?php 
    if(isset($is_edit))
    {?>
    
        $('#invoice-no').jqxInput('val', {label: '<?php echo $data_edit[0]['invoice_number'] ?>', value: '<?php echo $data_edit[0]['invoice'] ?>'});
    
    <?php    
    }
    ?>
    
    //=================================================================================
    //
    //   PO Product Grid
    //
    //=================================================================================
    $("#invoice-product-grid").on("bindingcomplete", function(event){
        var culture = {};
        culture.currencysymbol = "Rp. ";
        $("#invoice-product-grid").jqxGrid('localizestrings', culture);
        
        var rows = $("#invoice-product-grid").jqxGrid('getrows');
        var amount = 0;
        for(var i=0;i<rows.length;i++)
        {
            amount += rows[i].unit_price * rows[i].qty;
        }
        var culture = {};
        culture.currencysymbol = "Rp. ";
        culture.currencysymbolposition = "before";
        culture.decimalseparator = '.';
        culture.thousandsseparator = ',';
        $("#untaxed-amount").html(dataAdapter.formatNumber(amount, "c2", culture));
        var tax = 0;
        if($("#use-tax").is(":checked"))
        {
            tax = amount * 0.1;
        }
        $("#tax-amount").html(dataAdapter.formatNumber(tax, "c2", culture));
        $("#total-amount").html(dataAdapter.formatNumber((tax + amount), "c2", culture));
        
        $("#subtotal-value").val(amount);
        $("#tax-value").val(tax);
        $("#total-value").val((tax + amount));
    });
    
    var url = "";
    <?php 
    if(isset($is_edit))
    {?>
        url = "<?php echo base_url()?>invoice/get_detail_invoice?id=<?php echo $data_edit[0]['invoice']; ?>";
    <?php    
    }
    ?>
    
    <?php 
    if(isset($from_po))
    {?>
        url = "<?php echo base_url()?>invoice/get_detail_invoice?id=<?php echo $data_edit[0]['invoice']; ?>";
    <?php    
    }
    ?>
    
    var source =
    {
        datatype: "json",
        datafields:
        [
            { name: 'id_product'},
            { name: 'product_category'},
            { name: 'merk'},
            { name: 'product_code'},
            { name: 'product_name'},
            { name: 'name'},
            { name: 'unit_name', value: 'unit', values: { source: unitAdapter.records, value: 'id_unit_measure', name: 'name' } },
            { name: 'unit'},            
            { name: 'category_name'},
            { name: 'qty', type: 'number'},
            { name: 'unit_price', type: 'number'},
            { name: 'total_price', type: 'number'}
        ],
        id: 'id_product',
        url: url ,
        root: 'data'
    };
    var dataAdapter = new $.jqx.dataAdapter(source);
    $("#invoice-product-grid").jqxGrid(
    {
        theme: $("#theme").val(),
        <?php if(isset($is_view)){ echo 'disabled: true,';} ?>
        width: '100%',
        height: 250,
        selectionmode : 'singlerow',
        source: dataAdapter,
        columnsresize: true,
        autoshowloadelement: false,                                                                                
        sortable: true,
        autoshowfiltericon: true,
        rendertoolbar: function (toolbar) {
            $("#add-product").click(function(){
                var offset = $("#remove-product").offset();
                $("#select-product-popup").jqxWindow({ position: { x: parseInt(offset.left) + $("#remove-product").width() + 20, y: parseInt(offset.top)} });
                $("#select-product-popup").jqxWindow('open');
            });
            $("#remove-product").click(function(){
                var selectedrowindex = $("#invoice-product-grid").jqxGrid('getselectedrowindex');
                if (selectedrowindex >= 0) {
                    var id = $("#invoice-product-grid").jqxGrid('getrowid', selectedrowindex);
                    var commit1 = $("#invoice-product-grid").jqxGrid('deleterow', id);
                }
                
            });
        },
        columns: [
            { text: 'Product Code', dataField: 'product_code'},
            { text: 'Product', dataField: 'product_name'},
            { text: 'Unit', dataField: 'unit', displayfield: 'unit_name', columntype: 'dropdownlist',
                createeditor: function (row, value, editor) {
                    editor.jqxDropDownList({ source: unitAdapter, displayMember: 'name', valueMember: 'id_unit_measure' });
                }},
            { text: 'Quantity', dataField: 'qty', cellsformat: 'd2'}, 
            { text: 'Unit Price', dataField: 'unit_price',cellsformat: 'c2',
                validation: function (cell, value) {
                    if (value < 0) {
                      return { result: false, message: "Price should be greate than 0" };
                    }
                    return true;
                }
            },
            { text: 'Total Price', dataField: 'total_price', 
                cellsrenderer: function (index, datafield, value, defaultvalue, column, rowdata) {
                    var total = parseFloat(rowdata.unit_price) * parseFloat(rowdata.qty);
                    var culture = {};
                    culture.currencysymbol = "Rp. ";
                    culture.currencysymbolposition = "before";
                    culture.decimalseparator = '.';
                    culture.thousandsseparator = ',';
                    return "<div style='margin: 4px;' class='jqx-right-align'>" + dataAdapter.formatNumber(total, "c2", culture) + "</div>";
                }
            }
        ]
    });
    
    $("#subtotal").jqxNumberInput({ width: '80%', height: '25px', symbol: 'Rp. ',  spinButtons: false, readOnly: true, promptChar: "", digits: 9, disabled: true });
    $("#subtotal").jqxNumberInput('val', 0);
    
    $("#tax").jqxNumberInput({ width: '80%', height: '25px', symbol: 'Rp. ',  spinButtons: false, readOnly: true, promptChar: "", digits: 9, disabled: true });
    $("#tax").jqxNumberInput('val', 0);
    
    $("#total").jqxNumberInput({ width: '90%', height: '25px', symbol: 'Rp. ',  spinButtons: false, readOnly: true, promptChar: "", digits: 9, disabled: true });
    $("#total").jqxNumberInput('val', 0);
    
    $("#payment").jqxNumberInput({ width: '80%', height: '25px', symbol: 'Rp. ',  spinButtons: false, readOnly: false, digits: 9, min: 0 <?php if(isset($is_view)){ echo ',disabled: true';} ?>});
    $("#payment").jqxNumberInput('val', 0);
    
    $("#payment-left").jqxNumberInput({ width: '80%', height: '25px', symbol: 'Rp. ',  spinButtons: false, readOnly: true, promptChar: "", digits: 9, disabled: true });
    $("#payment-left").jqxNumberInput('val', 0);
    
    $("#difference").on('change', function(event){
        var value = event.args.value;
        if(value < 0)
        {
            alert('Payment cannot be greater than payment payment left');
            $("#payment").jqxNumberInput('val', 0);
        }
    });
    <?php
    if(isset($from_po))
    {?>
        $("#subtotal").jqxNumberInput('val', <?php echo $invoice[0]['sub_total'] ?>);
        $("#tax").jqxNumberInput('val', <?php echo $invoice[0]['tax'] ?>);
        $("#total").jqxNumberInput('val', <?php echo $invoice[0]['total_price'] ?>);
        $("#payment-left").jqxNumberInput('val', <?php echo $invoice[0]['total_price'] ?>);
    <?php    
    }
    ?>
    
        
    
    
    $("#payment").on('valueChanged', function (event){
        var value = event.args.value;
        $("#difference").jqxNumberInput('val',$("#payment-left").jqxNumberInput('val') - value);
    });
    
    $("#difference").jqxNumberInput({ width: '80%', height: '25px', symbol: 'Rp. ',  spinButtons: false, readOnly: false, promptChar: "", disabled: true });
    $("#difference").jqxNumberInput('val', 0);
    
    //=================================================================================
    //
    //   Edit Mode
    //
    //=================================================================================
    <?php
    if(isset($is_edit))
    {?>
    $("#subtotal").jqxNumberInput('val', <?php echo $data_edit[0]['sub_total'] ?>);
    $("#tax").jqxNumberInput('val', <?php echo $data_edit[0]['tax'] ?>);
    $("#total").jqxNumberInput('val', <?php echo $data_edit[0]['total_price'] ?>);
    $("#payment").jqxNumberInput('val', <?php echo $data_edit[0]['total_payment'] ?>);
    $("#difference").jqxNumberInput('val',$("#payment-left").jqxNumberInput('val') - $("#payment").jqxNumberInput('val'));
    <?php
    }
    ?>
    
    $("#validate-payment").click(function(){
        
        if($("#difference").val() < 0)
        {
            alert("Cannot save data. Difference should be greater than 0");
            throw '';
        }
        var data_post = {};
        
        data_post['note'] = $("#notes").html();
        data_post['date'] = $("#delivery-date").val('date').format('yyyy-mm-dd');
        data_post['id_invoice'] = $("#invoice-no").val().value;
        data_post['total_payment'] = $("#payment").val();
        data_post['difference'] = $("#difference").val();
        
        data_post['is_edit'] = $("#is_edit").val(); 
        data_post['id_invoice_receipt'] = $("#id_invoice_receipt").val(); 
		
        var payment_method = $("input[name='payment']:checked").val();
        data_post['payment_method'] = payment_method;
        data_post['rekening'] = $("#rekening").val()
        data_post['action_condition_identifier'] = 'validate';
        //alert(JSON.stringify(data_post));
        load_content_ajax(GetCurrentController(), 'save_edit_invoice_receipt', data_post);
    });
    
     $("#cancel-payment").click(function(){
        var data_post = {};
        data_post['id_payment'] = $("#id_invoice_receipt").val();
        load_content_ajax(GetCurrentController(), 'cancel_invoice_receipt', data_post);
    });
    
    //=================================================================================
    //
    //   Payment History Grid
    //
    //=================================================================================
    
    $("#payment-history-grid").on("bindingcomplete", function(event){
        var culture = {};
        culture.currencysymbol = "Rp. ";
        $("#payment-history-grid").jqxGrid('localizestrings', culture);
    });
    
    var urlHistory = "";
    <?php 
    if(isset($is_edit))
    {?>
        urlHistory = "<?php echo base_url()?>payment_receipt/get_payment_receipt_history?id_po=<?php echo $data_edit[0]['po']; ?>" + "&id_payment=<?php echo $data_edit[0]['id_payment_receipt']; ?>";
    <?php    
    }
    ?>
    
    <?php 
    if(isset($from_po))
    {?>
        urlHistory = "<?php echo base_url()?>payment_receipt/get_payment_receipt_history?id_po=<?php echo $po[0]['id_po']; ?>";
    <?php    
    }
    ?>
    
    var sourceHistory =
    {
        datatype: "json",
        datafields:
        [
            { name: 'id_payment_receipt'},
            { name: 'payment_date', type: 'date'},
            { name: 'total_payment', type: 'number'},
            { name: 'payment_receipt_number'},
            { name: 'payment_method'}
        ],
        id: 'id_payment_receipt',
        url: urlHistory ,
        root: 'data'
    };
    var dataAdapterHistory = new $.jqx.dataAdapter(sourceHistory);
    $("#payment-history-grid").jqxGrid(
    {
        theme: $("#theme").val(),
        width: '100%',
        height: 250,
        selectionmode : 'singlerow',
        source: dataAdapterHistory,
        columnsresize: true,
        autoshowloadelement: false,                                                                                
        sortable: true,
        autoshowfiltericon: true,
        columns: [
            { text: 'Payment Number', dataField: 'invoice_receipt_number'},
            { text: 'Payment Date', dataField: 'payment_date', cellsformat: 'dd/MM/yyyy'},
            { text: 'Total Payment', dataField: 'total_payment', cellsformat: 'c2'},
            { text: 'Payment Method', dataField: 'payment_method'},
        ]
    });
    
    
    
    $("#jqxExpander").jqxExpander({ width: '100%', expanded: false});
    
    function get_payment_left(id_invoice, id_payment)
    {
        var payment = "";
        if(id_payment != null)
        {
            payment = "&id_payment=" + id_payment;
        }
        
        var urlAjax = "<?php echo base_url() ?>invoice_receipt/get_payment_left?id_invoice=" + id_invoice + payment;
        var data_post = {};
        $.ajax({
            url: urlAjax,
    		type: "POST",
    		data: data_post,
    		success: function(output){
                try
                {
                    obj = JSON.parse(output);
                }
                catch(err)
                {
                    alert('Fatal error is happening with message : ' + output + '=====> Please contact your system administrator.');
                }
                $("#payment-left").jqxNumberInput('val', JSON.stringify(obj));
                $("#difference").jqxNumberInput('val',$("#payment-left").jqxNumberInput('val') - $("#payment").jqxNumberInput('val'));
                $(window).scrollTop(0);
    		},
            error: function( jqXhr ) 
            {

            }
        });
    }
    

    $("#payment-transfer").prop('checked', true);
    
    <?php
    if(isset($is_edit))
    {
        if($data_edit[0]['payment_method'] == 'transfer')
        {?>                        
            $("#payment-transfer").prop('checked', true);
        <?php
        }
        else if($data_edit[0]['payment_method'] == 'cash')
        {
        ?>
            $("#payment-cash").prop('checked', true);
        <?php       
        }           
        ?>
    
    <?php    
    } 
    ?>            
                
    $("#payment-cash").change(function(){
        $("#rekening-tr").css('display', 'none');
    });
    
    $("#payment-transfer").change(function(){
        $("#rekening-tr").css('display', 'block');
    });
    
    <?php
    if(isset($is_edit))
    {?>                       
        get_payment_left(<?php echo $data_edit[0]['invoice'] ?>, <?php echo $data_edit[0]['id_invoice_receipt'] ?>);
    <?php    
    } 
    ?> 
    
    <?php
    if(isset($from_po))
    {?>                   
        get_payment_left(<?php echo $po[0]['id_invoice'] ?>, null);
    <?php    
    } 
    ?> 

});

function SaveData()
{
    if($("#difference").val() < 0)
    {
        alert("Cannot save data. Difference should be greater than 0");
        throw '';
    }
    var data_post = {};
    <?php 
    if(isset($is_edit) && $data_edit[0]['status'] != 'void' || !isset($is_edit) )
    {
		if(!isset($is_edit) || (isset($is_edit) && $data_edit[0]['status'] == 'open'))
		{?>
        data_post['note'] = $("#notes").html();
        data_post['date'] = $("#delivery-date").val('date').format('yyyy-mm-dd');
        data_post['id_invoice'] = $("#invoice-no").val().value;
        data_post['total_payment'] = $("#payment").val();
        data_post['difference'] = $("#difference").val();
        
        data_post['is_edit'] = $("#is_edit").val(); 
        data_post['id_invoice_receipt'] = $("#id_invoice_receipt").val(); 
		
        var payment_method = $("input[name='payment']:checked").val();
        data_post['payment_method'] = payment_method;
        data_post['rekening'] = $("#rekening").val()
		alert(JSON.stringify(data_post));
        load_content_ajax(GetCurrentController(), 'save_edit_invoice_receipt', data_post);
		<?php 
		}
		else
		{
			if($data_edit[0]['status'] == 'close' || $data_edit[0]['status'] == 'cancel')
			{?>
			load_content_ajax('administrator', 'view_invoice_receipt' , null);
			<?php    
			}
		}
    ?>
    <?php   
    }
    ?>    
    
}
function DiscardData()
{
    load_content_ajax('administrator', 'view_invoice_receipt' , null);
}

</script>
<input type="hidden" id="prevent-interruption" value="true" />
<input type="hidden" id="is_edit" value="<?php echo (isset($is_edit) ? 'true' : 'false') ?>" />
<input type="hidden" id="id_invoice_receipt" value="<?php echo (isset($is_edit) ? $data_edit[0]['id_invoice_receipt'] : '') ?>" />
<div class="document-action">
    
    <?php 
    if(!isset($is_view))
    {?>
    <?php 
    if(!isset($is_edit) || (isset($is_edit) && $data_edit[0]['status'] != 'close' && $data_edit[0]['status'] != 'cancel'))
    {?>
    <button id="validate-payment">Receive Payment</button>
    <?php    
    }
    ?>
    
    <?php 
    if(isset($is_edit) && $data_edit[0]['status'] != 'open' && $data_edit[0]['status'] != 'cancel')
    {?>
    <button id="cancel-payment">Cancel Payment</button>
    <?php    
    }
    ?>
    <?php
    }
    ?>
    
    
    <ul class="document-status">
        <li <?php echo (isset($is_edit) && $data_edit[0]['status'] == 'open' ? 'class="status-active"' : '') ?> >
            <span class="label">Open</span>
            <span class="arrow">
                <span></span>
            </span>
        </li>
        <li <?php echo (isset($is_edit) && $data_edit[0]['status'] == 'close' ? 'class="status-active"' : '') ?>>
            <span class="label">Close</span>
            <span class="arrow">
                <span></span>
            </span>
        </li>
        <li <?php echo (isset($is_edit) && $data_edit[0]['status'] == 'cancel' ? 'class="status-active"' : '') ?>>
            <span class="label">Cancel</span>
            <span class="arrow">
                <span></span>
            </span>
        </li>
    </ul>
</div>
<div id='form-container' style="font-size: 13px; font-family: Arial, Helvetica, Tahoma">
    <div class="form-center" style="padding: 30px;">
        <div><h1 style="font-size: 18pt; font-weight: bold;">Invoice Receipt / <span><?php echo (isset($is_edit) ? $data_edit[0]['invoice_receipt_number'] : ''); ?></span></h1></div>
        <div>
            <table class="table-form">
                <tr>
                    <td>
                        <div class="label">
                            Invoice No.
                        </div>
                        <div class="column-input" colspan="2">
                            <input style="display:inline; width: 70%; font: -webkit-small-control; padding-left: 5px;" class="field" type="text" id="invoice-no" name="name" value="" disabled="true"/>
                            <?php if(!isset($is_edit)){?><button id="invoice-select">...</button><?php } ?>
                        </div>
                    </td>
                    <td>
                        <div class="label">
                            Date
                        </div>
                        <div class="column-input" colspan="2">
                            <div id="delivery-date" style="display: inline-block;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">
                            Subtotal
                        </div>
                        <div class="column-input" colspan="2">
                            <div id="subtotal" style="display: inline-block;"></div>
                        </div>
                    </td>
                    <td>
                        <div class="label">
                            Tax
                        </div>
                        <div class="column-input" colspan="2">
                            <div id="tax" style="display: inline-block;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="label">
                            Total
                        </div>
                        <div class="column-input" colspan="2">
                            <div id="total" style="display: inline-block;"></div>
                        </div>
                    </td>
                </tr>
                 <tr>
                    <td style="width: 80%" colspan="2">
                        <div id='jqxExpander'>
                            <div>
                                Product Detail
                            </div>
                            <div>
                                <div id="invoice-product-grid"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">                       
                         <div class="row-color" style="width: 100%; padding: 3px;">
                            <div style="display: inline;"><span>Payment History</span></div>
                        </div>
                    </td>
                </tr>
                 <tr>
                    <td style="width: 80%" colspan="2">
                        <div id="payment-history-grid"></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="width: 90%;">
                        <div class="label">
                            Payment Left
                        </div>
                        <div class="column-input" colspan="2">
                            <div id="payment-left" style="display: inline-block;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">
                            Payment
                        </div>
                        <div class="column-input" colspan="2">
                            <div id="payment" style="display: inline-block;"></div>
                        </div>
                    </td>
                    <td>
                        <div class="label">
                            Difference
                        </div>
                        <div class="column-input" colspan="2">
                            <div id="difference" style="display: inline-block;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">                       
                         <div class="row-color" style="width: 100%; padding: 3px;">
                            <div style="display: inline;"><span>Payment Method</span></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">
                            Transfer
                        </div>
                        <div class="column-input" colspan="2">
                            <input <?php if(isset($is_view)){ echo 'disabled="true"';} ?> type="radio" name="payment" value="transfer" id="payment-transfer" /> 
                        </div>
                    </td>
                    <td>
                        <div class="label">
                            Cash
                        </div>
                        <div class="column-input" colspan="2">
                            <input <?php if(isset($is_view)){ echo 'disabled="true"';} ?> type="radio" name="payment" value="cash" id="payment-cash"/> 
                        </div>
                    </td>
                </tr>
                <tr id="rekening-tr">
                    <td>
                        <div class="label">
                            Rekening                                       
                        </div>
                        <div class="column-input" colspan="2" style="width: 100%;">
                            <input class="field" type="text" id="rekening" value="<?php echo (isset($is_edit) ? $data_edit[0]['rekening'] : '') ?>" />
                        </div>
                    </td>
                    <td>
                       
                    </td>
                </tr>
                <tr>
                    <td style="width: 80%;padding-top: 20px;" colspan="2">
                        <div class="label">
                            Notes
                        </div>
                        <textarea <?php if(isset($is_view)){ echo 'disabled=disabled';} ?> class="field" cols="10" rows="20" style="height: 50px;"></textarea>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div id="select-invoice-popup">
    <div>Select Invoice</div>
    <div>
        <table class="table-form">
            <tr>
                <td style="width: 80%" colspan="2">
                    <div id="select-invoice-grid"></div>
                </td>
            </tr>
        </table>
    </div>
</div>