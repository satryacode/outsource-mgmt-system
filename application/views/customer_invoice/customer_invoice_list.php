<script>
 $(document).ready(function () {
        var url = "<?php echo base_url() ;?>customer_invoice/get_customer_invoice_list";
         var source =
            {
                datatype: "json",
                datafields:
                [
                    { name: 'id_customer_invoice'},
                    { name: 'so'},
                    { name: 'so_number'},
                    { name: 'customer_name'},
                    { name: 'contract_number'},
                    { name: 'payment_date', type: 'date'},
                    { name: 'customer_invoice_number'},
                    { name: 'status'},
                    { name: 'total_price', type: 'number'},
                    { name: 'total_payment', type: 'number'},
                    
                ],
                id: 'id_customer_invoice',
                url: url,
                root: 'data'
            };
            var cellclass = function (row, columnfield, value) 
            {
                if (value == 'close') {
                    return 'green';
                }
            }
            var dataAdapter = new $.jqx.dataAdapter(source);
            $('#jqxgrid').jqxGrid(
            {
                theme: $("#theme").val(),
                width: '100%',
                height: 450,
                source: dataAdapter,
                groupable: true,
                columnsresize: true,
                autoshowloadelement: false,                                                                                
                filterable: true,
                showfilterrow: true,
                sortable: true,
                autoshowfiltericon: true,
                columns: [
                    { text: 'Invoice No.', dataField: 'customer_invoice_number', width: 200},
                    { text: 'Sales Order', dataField: 'po_number', width: 200},
                    { text: 'Contract No.', dataField: 'contract_number', width: 200},
                    { text: 'Date', dataField: 'payment_date', cellsformat: 'dd/MM/yyyy',filtertype: 'date'},
                    { text: 'Total Price', dataField: 'total_price', width: 200, cellsformat: 'c2'},
                    { text: 'Total Payment', dataField: 'total_payment', width: 200, cellsformat: 'c2'},
                    { text: 'Status', dataField: 'status', width: 100, cellclassname: cellclass}
                    
                ]
            });
            
            $("#jqxgrid").on("bindingcomplete", function (event) {
                var localizationobj = {};
                localizationobj.currencysymbol = "Rp. ";
                $("#jqxgrid").jqxGrid('localizestrings', localizationobj); 
            });            
        });  
</script>
<script>
function CreateData()
{
    load_content_ajax(GetCurrentController(), 190, null, null);
}

function EditData()
{
    var row = $('#jqxgrid').jqxGrid('getrowdata', parseInt($('#jqxgrid').jqxGrid('getselectedrowindexes')));
    if(row != null)
    {
        var data_post = {};
        var param = [];
        var item = {};
        item['paramName'] = 'id';
        item['paramValue'] = row.id_customer_invoice;
        param.push(item);        
        data_post['id_customer_invoice'] = row.id_customer_invoice;
        load_content_ajax(GetCurrentController(), 191 ,data_post, param);
    }
    else
    {
        alert('Select menu you want to edit first');
    }                            
}

function DeleteData()
{
    var row = $('#jqxgrid').jqxGrid('getrowdata', parseInt($('#jqxgrid').jqxGrid('getselectedrowindexes')));
        
    if(row != null)
    {
       if(confirm("Are you sure you want to delete menu : " + row.name))
        {
            var data_post = {};
            data_post['id_application_action'] = row.id_application_action;
            //load_content_ajax(GetCurrentController(), 104 ,data_post);
        }
    }
    else
    {
        alert('Select menu you want to delete first');
    }
}

</script>
<style>
.green {
    color: green;
}
</style>
<div id='form-container' style="font-size: 13px; font-family: Arial, Helvetica, Tahoma">
    <div class="form-full">
        <div id="jqxgrid">
        </div>
    </div>
</div>