<?php
class MGS_Mpanel_Block_Catalog_Layer_Filter_Price extends Mage_Catalog_Block_Layer_Filter_Price 
{
    	
	public $_currentCategory;
	public $_searchSession;
	public $_productCollection;
	public $_maxPrice;
	public $_minPrice;
	public $_currMinPrice;
	public $_currMaxPrice;
	public $_imagePath;
	
	
	/*
	* 
	* Set all the required data that our slider will require
	* Set current _currentCategory, _searchSession, setProductCollection, setMinPrice, setMaxPrice, setCurrentPrices, _imagePath
	* 
	* @set all required data
	* 
	*/
	public function __construct(){
	
		if($this->getSliderStatus()){
			$this->_currentCategory = Mage::registry('current_category');
			$this->_searchSession = Mage::getSingleton('catalogsearch/session');
			$this->setProductCollection();
			$this->setMinPrice();
			$this->setMaxPrice();
			$this->setCurrentPrices();
			$this->_imagePath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'mpanel/slider/';
			
			parent::__construct();
		}
		else{
			parent::__construct();
			$this->_filterModelName = 'catalog/layer_filter_price';
		}
	}
	
	/*
	* 
	* Check whether the slider is enabled.
	*
	* @return boolean
	* 
	*/
	public function getSliderStatus(){
		if(Mage::getStoreConfig('mpanel/catalog/price_slider') && Mage::getStoreConfig('mpanel/general/enabled'))
			return true;
		else
			return false;			
	}
	 
	
	/*
	* Fetch Styles for price text Box
	*
	* @return styles
	*/
	public function getPriceBoxStyle(){
		return '';
	}
	
	public function getGoBtnText(){
		$name = "Go";
		return $name;
	}

	public function getGoBtnStyle(){
		return '';
	}
	
	public function isTextBoxEnabled(){
		return 0;	
	}
	
	public function setNewPrices(){
		$this->setInSession('_newCurrMinPrice', $this->_currMinPrice);	
		$this->setInSession('_newCurrMaxPrice', $this->_currMaxPrice);	
		if(!is_numeric($this->_currMinPrice)){
			$this->_currMinPrice = 0;
			$this->setInSession('_currMinPrice', 0);
		}
		
		if(!is_numeric($this->_currMaxPrice)){
			$this->_currMaxPrice = 0;
			$this->setInSession('_currMaxPrice', 0);
		}
		
		$sMin = $this->getFromSession('_minPrice');
		$sMax = $this->getFromSession('_maxPrice');
		$csMin = $this->getFromSession('_currMinPrice');
		$csMax = $this->getFromSession('_currMaxPrice');
		$ncsMin = $this->getFromSession('_newCurrMinPrice');
		$ncsMax = $this->getFromSession('_newCurrMaxPrice');
		
		
		// if Filters are called
			
		$a[0][] = 'price_index.min_price';
		$a[0][] = 'ASC';
		$loadedCollection = $this->getLayout()->getBlockSingleton('catalog/product_list')->getLoadedProductCollection()->setOrder('min_price','DESC')->getSelect()->setPart('order',$a)->query()->fetchAll();
		//print_r(get_class_methods($loadedCollection));exit;
		//echo '<pre>';
		$tot = count($loadedCollection);
		
		//echo $loadedCollection;exit;
		
		if(count($loadedCollection) > 0){
			$loadedMin = $loadedCollection[0]['min_price'];
			$loadedMax = $loadedCollection[$tot-1]['min_price'];	
		}	
		
		
		
				
		if($this->_currMinPrice == $csMin && $this->_currMaxPrice == $csMax){
			
			if($this->_minPrice != $ncsMin){
				$this->setInSession('_minPrice', $loadedMin);
				$this->_minPrice = $loadedMin;
			}
			if($loadedMin >= $csMin){
				$this->_currMinPrice = $loadedMin;
				$this->setInSession('_currMinPrice', $loadedMin);
			}
			if($this->_maxPrice != $ncsMax){
				$this->setInSession('_maxPrice', $loadedMin);
				$this->_maxPrice = $loadedMax;
			}
			if($loadedMax <= $csMax){
				$this->_currMaxPrice = $loadedMax;
				$this->setInSession('_currMaxPrice', $loadedMax);
			}
		}else{
			if($ncsMin == $loadedMin){
				$this->setInSession('_minPrice', $loadedMin);
				$this->_minPrice = $loadedMin;
			}
			if($ncsMax == $loadedMax){
				$this->setInSession('_maxPrice', $loadedMin);
				$this->_maxPrice = $loadedMax;
			}
			
			
				
		}
	}
	
	public function getPriceDisplayType(){
		$html = '<p class="label-box">
					<input type="text" id="amount" readonly="readonly" value="'.Mage::helper('core')->currency($this->getCurrMinPrice(),true,false)." - ".Mage::helper('core')->currency($this->getCurrMaxPrice(),true,false).'" />
					</p>';	
		return $html;
	}
	
	/**
	*
	* Prepare html for slider and add JS that incorporates the slider.
	*
	* @return html
	*
	*/
	
	public function getHtml(){
		
		if($this->getSliderStatus()){
			$this->setNewPrices();
			$text='
				<div class="price">
					<div id="slider-range"></div>
					'.$this->getPriceDisplayType().'
				</div>'.$this->getSliderJs();	
			
			return $text;
		}
		return parent::_toHtml();
	}
	
	/*
	* Prepare query string that was in the original url 
	*
	* @return queryString
	*/
	public function prepareParams(){
		$url="";
	
		$params=$this->getRequest()->getParams();
		foreach ($params as $key=>$val)
			{
					if($key=='id'){ continue;}
					if($key=='min'){ continue;}
					if($key=='max'){ continue;}
					$url.='&'.$key.'='.$val;		
			}		
		return $url;
	}
	
	/*
	* Fetch Current Currency symbol
	* 
	* @return currency
	*/
	public function getCurrencySymbol(){
		return Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
	}
	
	/*
	* Fetch Current Minimum Price
	* 
	* @return price
	*/
	public function getCurrMinPrice(){
		if($this->_currMinPrice > 0){
			$min = $this->_currMinPrice;
		} else{
			$min = $this->_minPrice;
		}
		return $min;
	}
	
	/*
	* Fetch Current Maximum Price
	* 
	* @return price
	*/
	public function getCurrMaxPrice(){
		if($this->_currMaxPrice > 0){
			$max = $this->_currMaxPrice;
		} else{
			$max = $this->_maxPrice;
		}
		return $max;
	}
	
	/*
	* Get Slider Configuration TimeOut
	* 
	* @return timeout
	*/
	public function getConfigTimeOut(){
		return '0';
	}
	
	
	/*
	* Gives you the current url without parameters
	* 
	* @return url
	*/
	public function getCurrentUrlWithoutParams(){
		$baseUrl = explode('?',Mage::helper('core/url')->getCurrentUrl());
		$baseUrl = $baseUrl[0];
		return $baseUrl;
	}
	
	/*
	* Check slider Ajax enabled
	* 
	* @return boolean
	*/
	public function isAjaxSliderEnabled(){
		return 1;
	}
	
	
	public function getOnSlideCallbacks(){
		return '';	
	}
	
	/*
	* Get JS that brings the slider in Action
	* 
	* @return JavaScript
	*/
	public function getSliderJs(){
		
		$baseUrl = $this->getCurrentUrlWithoutParams();
		$timeout = 0;
		
		if($this->isAjaxSliderEnabled()){
			$ajaxCall = 'sliderAjax(url);';
		}else{
			$ajaxCall = 'window.location=url;';
		}
		$updateTextBoxPriceJs = '';
		if($this->isTextBoxEnabled()){
			$updateTextBoxPriceJs = '
				mgsjQuery("#minPrice").val(newMinPrice); 
				mgsjQuery("#maxPrice").val(newMaxPrice);';
		}
		
		
		$html = '
			<script type="text/javascript">
				mgsjQuery(function($) {
					var newMinPrice, newMaxPrice, url, temp;
					var categoryMinPrice = '.$this->_minPrice.';
					var categoryMaxPrice = '.$this->_maxPrice.';
					function isNumber(n) {
					  return !isNaN(parseFloat(n)) && isFinite(n);
					}
					
					$(".priceTextBox").focus(function(){
						temp = $(this).val();	
					});
					
					$(".priceTextBox").keyup(function(){
						var value = $(this).val();
						if(!isNumber(value)){
							$(this).val(temp);	
						}
					});
					
					$(".priceTextBox").keypress(function(e){
						if(e.keyCode == 13){
							var value = $(this).val();
							if(value < categoryMinPrice || value > categoryMaxPrice){
								$(this).val(temp);	
							}
							url = getUrl($("#minPrice").val(), $("#maxPrice").val());
							'.$ajaxCall.'	
						}	
					});
					
					$(".priceTextBox").blur(function(){
						var value = $(this).val();
						if(value < categoryMinPrice || value > categoryMaxPrice){
							$(this).val(temp);	
						}
						
					});
					
					$(".go").click(function(){
						url = getUrl($("#minPrice").val(), $("#maxPrice").val());
						'.$ajaxCall.'	
					});
					
					$( "#slider-range" ).slider({
						range: true,
						min: categoryMinPrice,
						max: categoryMaxPrice,
						values: [ '.$this->getCurrMinPrice().', '.$this->getCurrMaxPrice().' ],
						slide: function( event, ui ) {
							newMinPrice = ui.values[0];
							newMaxPrice = ui.values[1];
							
							$( "#amount" ).val( "'.$this->getCurrencySymbol().'" + newMinPrice + " - '.$this->getCurrencySymbol().'" + newMaxPrice );
							
							'.$updateTextBoxPriceJs.'
							
						},stop: function( event, ui ) {
							
							// Current Min and Max Price
							var newMinPrice = ui.values[0];
							var newMaxPrice = ui.values[1];
							
							// Update Text Price
							$( "#amount" ).val( "'.$this->getCurrencySymbol().'"+newMinPrice+" - '.$this->getCurrencySymbol().'"+newMaxPrice );
							
							'.$updateTextBoxPriceJs.'
							
							url = getUrl(newMinPrice,newMaxPrice);
							if(newMinPrice != '.$this->getCurrMinPrice().' && newMaxPrice != '.$this->getCurrMaxPrice().'){
								clearTimeout(timer);
								//window.location= url;
								
							}else{
									timer = setTimeout(function(){
										'.$ajaxCall.'
									}, '.$timeout.');     
								}
						}
					});
					
					function getUrl(newMinPrice, newMaxPrice){
						return "'.$baseUrl.'"+"?min="+newMinPrice+"&max="+newMaxPrice+"'.$this->prepareParams().'";
					}
				});
			</script>';	
		
		return $html;
	}
	
	
	/*
	* Get the Slider config 
	*
	* @return object
	*/
	public function getConfig($key){
		return Mage::getStoreConfig($key);
	}
	
	
	/*
	* Set the Actual Min Price of the search and catalog collection
	*
	* @use category | search collection
	*/
	public function setMinPrice(){
		if( (isset($_GET['q']) && !isset($_GET['min'])) || !isset($_GET['q'])){
			
			if(Mage::getVersion() < '1.7.0.2'){
				$this->_productCollection->getSelect()->reset('order');
				$this->_productCollection->getSelect()->order('final_price','asc');
				$this->_minPrice = round($this->_productCollection->getFirstItem()->getFinalPrice());
			}else{
				$this->_minPrice = floor($this->_productCollection->getMinPrice());
			}

			$this->_searchSession->setMinPrice($this->_minPrice);		
		}else{
			$this->_minPrice = $this->_searchSession->getMinPrice();	
		}
		
	}
	
	/*
	* Set the Actual Max Price of the search and catalog collection
	*
	* @use category | search collection
	*/
	public function setMaxPrice(){
		if( (isset($_GET['q']) && !isset($_GET['max'])) || !isset($_GET['q'])){
			
			if(Mage::getVersion() < '1.7.0.2'){
				$this->_productCollection->getSelect()->reset('order');
				$this->_productCollection->getSelect()->order('final_price','asc');
				$this->_maxPrice = round($this->_productCollection->getLastItem()->getFinalPrice());
			}else{
				$this->_maxPrice = ceil($this->_productCollection->getMaxPrice());	
			}
			
			$this->_searchSession->setMaxPrice($this->_maxPrice);
		}else{
			$this->_maxPrice = $this->_searchSession->getMaxPrice();
		}
		
		
	}
	
	/*
	* Set the Product collection based on the page server to user 
	* Might be a category or search page
	*
	* @set /*
	* Set the Product collection based on the page server to user 
	* Might be a category or search page
	*
	* @set Mage_Catalogsearch_Model_Layer 
	* @set Mage_Catalog_Model_Layer    
	*/
	public function setProductCollection(){
		
		if($this->_currentCategory){
			$this->_productCollection = $this->_currentCategory
							->getProductCollection()
							->addAttributeToSelect('*')
							->setOrder('price', 'ASC');
		}else{
			$this->_productCollection = Mage::getSingleton('catalogsearch/layer')->getProductCollection()	
							->addAttributeToSelect('*')
							->setOrder('price', 'ASC');
		}					
	}
	
	
	/*
	* Set Current Max and Min Prices choosed by the user
	*
	* @set price
	*/
	public function setCurrentPrices(){
		
		$this->_currMinPrice = $this->getRequest()->getParam('min');
		$this->_currMaxPrice = $this->getRequest()->getParam('max');
	}	
	
	/*
	* Set Current Max and Min Prices choosed by the user
	*
	* @set price
	*/
	public function baseToCurrent($srcPrice){
		$store = $this->getStore();
        return $store->convertPrice($srcPrice, false, false);
	}
	
	
	public function setInSession($var, $value){
		$set = "set".$var;
		Mage::getSingleton('catalog/session')->$set($value);	
	}
	
	public function getFromSession($var){
		$get = "get".$var;
		return Mage::getSingleton('catalog/session')->$get();	
	}
	
	/*
	* Retrive store object
	*
	* @return object
	*/
	public function getStore(){
		return Mage::app()->getStore();
	}
}
