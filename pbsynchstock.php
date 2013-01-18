<?php

if (!defined('_PS_VERSION_'))
	exist;

class PbSynchStock extends Module
{

	var $export_stock_query = "select ps_stock_available.id_stock_available as id_stock_available,ps_stock_available.id_product,ps_product_lang.name as name,ps_attribute_lang.name as size,ps_stock_available.quantity as quantity 
										from ps_stock_available
										join ps_product_lang on ps_stock_available.id_product = ps_product_lang.id_product
										left join ps_product_attribute_combination on ps_stock_available.id_product_attribute = ps_product_attribute_combination.id_product_attribute
										left join ps_attribute_lang on ps_product_attribute_combination.id_attribute = ps_attribute_lang.id_attribute";

	public function __construct()
	{
		$this->name = 'pbsynchstock';
		$this->tab = 'quick_bulk_update';
		$this->version = '0.1';
		$this->author = 'Eric Fernance';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Purple Beanie Bulk Stock Update');
 	   	$this->description = $this->l('Helps to maintain stock levels.');
	}

	public function install()
	{
	  if (parent::install() == false)
	    return false;
	  return true;
	}


	/**
	* method called when the configure button is clicked in the module display
	* @return string the content that should be displayed.
	*/

	public function getContent()
	{
		
		$this->_html .= '<h2>'.$this->displayName.'</h2>';
		$this->_html .= '<p>'.$this->description.'</p>';

		if (isset($_POST['action']) && in_array($_POST['action'],array('downloaded_master_stock_list','update_stock_values')))
			$this->_html .= $this->_post_process($_POST['action']);
		else 
			$this->_html .= $this->_get_export_form();

		return $this->_html;
	}

	/**
	* builds and displays the form for exporting the content as csv.
	* @return string the html form fragment
	*/

	private function _get_export_form()
	{
		$html ='';							//empty place holder to hold the form in use.
		$html =  '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
						<table border="0">
							<tr><td>'.$this->l('Generates a .csv file with all the items and options').'</td></tr>
							<tr><td align="center"><input type="submit" class="button" name="get_master_stock_list" value="'.$this->l('View all items').'"/></td></tr>
						</table>
						<input type="hidden" name="action" value="downloaded_master_stock_list"/>
					</form>';

		return $html;

	}

	/**
	* processes the post request as needed
	* @param string the action
	* @return string the html output
	*/

	private function _post_process($action)
	{
		if ($action == 'downloaded_master_stock_list') {

			$html = '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="POST">';
			$html .= '<table border="0">';
			if ($_POST['action'] == "downloaded_master_stock_list") {
				$db = Db::getInstance();
				if ($results = $db->executeS($this->export_stock_query))
					foreach ($results as $row)
						$html .= '<tr><th>'.$row['name'].' - '.$row['size'].'</th><td> has stock of 
											<input type="text" size="4" name="stock_available[]" value="'.$row['quantity'].'"/>
											<input type="text" size="4" name="id_stock_available[]" value="'.$row['id_stock_available'].'"/></td></tr>';
				
				$html .='</table>';
				$html .='<input type="hidden" name="action" value="update_stock_values"/>';
				$html .='<input type="submit" class="button" name="" value="'.$this->l('Update Stock Items').'"/>';
				$html .='</form>';
				return $html;
			}
		} else {
			$db = Db::getInstance();
			foreach ($_POST['id_stock_available'] as $i=>$stock_available) {
				$db->update('stock_available',array('quantity'=>(int)$_POST['stock_available'][$i]), $where = 'id_stock_available = '.(int)$stock_available);
			}

			return $this->_get_export_form();
		}

	}
}


?>