<?php

require_once('lensesMain.php');

class lensesWarehouseHistory extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Warehouse History'=>''));
        $this->table = "warehouse_item_history";
        $this->title = "Warehouse History";
        $this->selected_menu = "warehouse_history";
        $this->freezePane = 3;
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->ajax_url = base_url('ajax/warehouse_history');
        
        $this->search_query = 'select a.id,ifnull(w1.name,"") warehouse_name,b.skucode,c.name product_name,d.name option_name,a.created_date,a.adj_quantity,a.adj_quantity2
            ,a.movement_type
            ,if(a.movement_type="S",concat("Sales ID: ",a.trans_id),
                    if(a.movement_type="R",concat("Sales ID: ",a.trans_id),
                            if(a.movement_type="T",concat("Transfered To [",w2.name,"] ",b2.skucode),
                                if(a.movement_type="U",concat("Received From [",w2.name,"]"),"")
                            )
                    )
            ) movement_message
            from warehouse_item_history a
            left join warehouse_item b on a.warehouse_item_id=b.id
            left join products c on b.product_id=c.id
            left join option_item d on c.option_id=d.option_id and b.item_id=d.id
            left join warehouses w1 on b.warehouse_id=w1.id
            left join warehouse_item b2 on a.trans_id=b2.id
            left join warehouses w2 on b2.warehouse_id=w2.id';
        
        $this->header = array(array('id'=>'id','name'=>'ID','filter-sorting'=>'desc'),array('id'=>'warehouse_name','name'=>'Warehouse'),array('id'=>'skucode','name'=>'SKU Code'),array('id'=>'product_name','name'=>'Frame'),array('id'=>'option_name','name'=>'Color'),array('id'=>'created_date','name'=>'Date'),array('id'=>'adj_quantity','name'=>'Storage A Quantity'),array('id'=>'adj_quantity2','name'=>'Storage B Quantity'),array('id'=>'movement_type','name'=>'Movement Type','option_text'=>array('A'=>'Adjustment','S'=>'Sales Entry','R'=>'Sales Return','T'=>'Item Transfered','U'=>'Received From')),array('id'=>'movement_message','name'=>'Message'));
    }
    
}