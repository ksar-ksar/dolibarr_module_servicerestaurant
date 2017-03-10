<?php 

	require('config.php');
	require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';
	require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
	
	dol_include_once('/product/class/product.class.php');
	dol_include_once('/commande/class/commande.class.php');
	
	dol_include_once('/servicerestaurant/class/servicerestaurant.class.php');
	
	$langs->load('servicerestaurant@servicerestaurant'); // Charge les clés de traductions du module
	$controllerServiceRestaurant = new ControllerServiceRestaurant($db,$conf,$user);
	
    include_once('include/menu.php'); 
    $action				= GETPOST('action','alpha');
    $fk_product			= GETPOST('fk_product','int');
    $fk_table			= GETPOST('fk_table','int');
    $fk_categorie		= GETPOST('fk_categorie','int');
    $button_table		= $controllerServiceRestaurant->buttonBackToTablePage();
    $button_dolibarr	= $controllerServiceRestaurant->buttonLeaveModule();
    $restaurantName		= $controllerServiceRestaurant->getRestaurant()->description;
    $categories			= $controllerServiceRestaurant->getAllProductsCategories();
    $table_name=$controllerServiceRestaurant->getTableName($fk_table);
    $T_Order=$controllerServiceRestaurant->getAllCommandesInvalidBySociete($db,$fk_table);
    if($action=='add')
    {
        $controllerServiceRestaurant->addProduct($fk_table,$fk_product);
    }
    if($action=='rem')
    {
        $controllerServiceRestaurant->removeProduct($fk_table,$fk_product);
    }
    if($action=='valid')
    {
        $controllerServiceRestaurant->validate_order($fk_table);
        header('Location:'.dol_buildpath("/servicerestaurant/tables.php",1));
    }
?>

      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">
              <img alt="logo" src="img/logo.png">
            </a>
          </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="navbar">
          <a class="navbar-brand" href="#"><?php echo $restaurantName; ?></a>
            <a class="navbar-brand text-center" style="width: 50%;" href="#">Commande <?php echo $table_name;?></a>


          <ul class="nav navbar-nav navbar-right">
            <li style="padding-top: 3%;"><?php echo $button_dolibarr ?></li>
            <li style="padding-top: 3%;"><?php echo $button_table; ?></li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
      </nav>

      <div class="main-wrap">
          <!-- Ouvrir / Fermer le menu sidebar catégories roduits -->
          <input id="slide-sidebar" type="checkbox" role="button" />
          <label for="slide-sidebar"></label>

          <!-- Menu sidebar catégories roduits -->
          <div class="sidebar">

            <ul>
            <?php 
            if($T_Order!= array())
            {
                echo '<a href="commande.php?fk_categorie=0&fk_table='.$fk_table.'" name="Résumé"><li>Résumé</li></a>';
            }

            foreach($categories as $cat)
            {
                $categorie=new Categorie($db);
                $categorie->fetch($cat);
                $label_categorie=$categorie->label;
                echo "<a href=\"commande.php?fk_categorie=$cat&fk_table=$fk_table\" name=\"$fk_categorie\"><li>$label_categorie</li></a>";
            }
            ?>
            </ul>
          </div>

          <!-- Titre -->
          <div class="main-container">


            <div id="allProducts" >
            <?php
            if($fk_categorie == 0) 
            {
                $Tproduct=$controllerServiceRestaurant->getProductFromOrder($fk_table);
                foreach($Tproduct as $product_id)
                {
                    $product=new Product($db);
                    $product->fetch($product_id);
                    $productStockReel=$product->stock_reel;
                    $productStock=$controllerServiceRestaurant->getProductStock($product_id);
                    $productQtyFromOrder=$controllerServiceRestaurant->getProductQuantityFromOrder($fk_table,$product_id);
                    $label_product=$product->label;
                    $description_product=$product->description;
                    if(!is_numeric($productStock))
                    {
                        $productStock=0;
                    }
                    if($productStock>0)
                    {
                        echo '<section id="section"class="col-lg-12 col-sm-12 produits" style="height: auto; margin-bottom: 50px; background-color: #d1d5d8; padding-top: 20px; padding-left: 10px; padding-right: 10px; padding-bottom: 20px;">';
                    }
                    if($productStock==0) 
                    {
                        echo '<section id="section"class="col-lg-12 col-sm-12 produits" style="height: auto; margin-bottom: 50px; background-color: #ea1c1c; padding-top: 20px; padding-left: 10px; padding-right: 10px; padding-bottom: 20px;">';
                    }
                    ?> 
                      <div class="col-lg-4 col-sm-12">
                        <h3 style="margin: 0px;"><?php echo $label_product; ?></h3>
                        <p><?php echo $description_product; ?></p>
                        <p><br><b>Stock disponible : <input type="text" name="stock" value="<?php echo $productStockReel.' ('.$productStock." restant(s))"; ?>" style="background-color: rgba(255,255,255,0); border: none;"></b></p>
                      </div>
                      <div class="col-lg-4 col-sm-12" style="height: 120px;">                        
                    <?php        
                    if($productStock>0)
                    {
                        echo '<textarea style="margin: 0px; height: 120px; width: 100%; border: none; padding: 15px;" class="col-lg-12 infos-sup" name="name" rows="8" cols="80" placeholder="Ajouter des informations complémentaires"></textarea>';
                    }
                    ?>
                      </div>
                      <div class="col-lg-4 col-sm-12">
                        <div class="col-lg-4 col-sm-4 moins" style="cursor: pointer; background-color: #3c8eb9; height: 120px; font-size: 5vmin; text-align: center; vertical-align: middle; line-height: 120px;">
                          <a href="commande.php?fk_categorie=0&action=rem&fk_product=<?php echo $product_id; ?>&fk_table=<?php echo $fk_table; ?>">-</a>
                       </div>
                        <div class="col-lg-4 col-sm-4 count" style="background-color: white; height: 120px; font-size: 5vmin; text-align: center; vertical-align: middle; line-height: 120px;">
                            <?php echo $productQtyFromOrder;?>
                        </div>
                        <div id="plus" class="col-lg-4 col-sm-4 plus" style="cursor: pointer; background-color: #3c8eb9; height: 120px; font-size: 5vmin; text-align: center; vertical-align: middle; line-height: 120px;">
                          <a href="commande.php?fk_categorie=0&action=add&fk_product=<?php echo $product_id; ?>&fk_table=<?php echo $fk_table; ?>">+</a>
                        </div>
                      </div>
                    </section>
                    <?php
                }
            }
            foreach($categories as $cat)
            {
                $categorie->fetch($cat);
                if($fk_categorie == $fk_categorie) {
                    $Tproduct=$controllerServiceRestaurant->getAllProductByCategorie($fk_categorie);
                    foreach($Tproduct as $product_id)
                    {
                        $product=new Product($db);
                        $product->fetch($product_id);
                        $productStockReel=$product->stock_reel;
                        $productStock=$controllerServiceRestaurant->getProductStock($product_id);
                        $productQtyFromOrder=$controllerServiceRestaurant->getProductQuantityFromOrder($fk_table,$product_id);
                        $label_product=$product->label;
                        $description_product=$product->description;
                        if(!is_numeric($productStock))
                        {
                            $productStock=0;
                        }
                        if($productStock>0)
                        {
                            echo '<section id="section"class="col-lg-12 col-sm-12 produits" style="height: auto; margin-bottom: 50px; background-color: #d1d5d8; padding-top: 20px; padding-left: 10px; padding-right: 10px; padding-bottom: 20px;">';
                        }
                        if($productStock==0) 
                        {
                            echo '<section id="section"class="col-lg-12 col-sm-12 produits" style="height: auto; margin-bottom: 50px; background-color: #ea1c1c; padding-top: 20px; padding-left: 10px; padding-right: 10px; padding-bottom: 20px;">';
                        }
                        ?> 
                            <div class="col-lg-4 col-sm-12">
                                <h3 style="margin: 0px;"><?php echo $label_product; ?></h3>
                                <p><?php echo $description_products; ?></p>
                                <p><br><b>Stock disponible : <input type="text" name="stock" value="<?php echo $productStockReel.' ('.$productStock." restant(s))"; ?>" style="background-color: rgba(255,255,255,0); border: none;"></b></p>
                            </div>
                            <div class="col-lg-4 col-sm-12" style="height: 120px;">
                        <?php        
                        if($productStock>0)
                        {
                            echo '<textarea style="margin: 0px; height: 120px; width: 100%; border: none; padding: 15px;" class="col-lg-12 infos-sup" name="name" rows="8" cols="80" placeholder="Ajouter des informations complémentaires"></textarea>';
                        }
                        ?>
                            </div>
                            <div class="col-lg-4 col-sm-12">
                                <div class="col-lg-4 col-sm-4 moins" style="cursor: pointer; background-color: #3c8eb9; height: 120px; font-size: 5vmin; text-align: center; vertical-align: middle; line-height: 120px;">
                                        <a href="commande.php?fk_categorie=<?php echo $fk_categorie; ?>&action=rem&fk_product=<?php echo $product_id; ?>&fk_table=<?php echo $fk_table; ?>">-</a>
                                </div>
                                <div class="col-lg-4 col-sm-4 count" style="background-color: white; height: 120px; font-size: 5vmin; text-align: center; vertical-align: middle; line-height: 120px;">
                                    <?php echo $productQtyFromOrder;?>
                                </div>
                                <div id="plus" class="col-lg-4 col-sm-4 plus" style="cursor: pointer; background-color: #3c8eb9; height: 120px; font-size: 5vmin; text-align: center; vertical-align: middle; line-height: 120px;">
                                    <a href="commande.php?fk_categorie=<?php echo $fk_categorie; ?>&action=add&fk_product=<?php echo $product_id; ?>&fk_table=<?php echo $fk_table; ?>">+</a>
                                </div>
                            </div>
                        </section>
                        <?php
                    }
                }
            }
            ?>
            </div> <!-- All Products -->
            
          </div>
      </div>

      <script src="less/dist/less.js" type="text/javascript"></script>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
      <script src="<?php echo dol_buildpath("/core/js/jnotify.js",1); ?>"></script>
      <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js" type="text/javascript"></script>
      <!-- <script src="js/color.js"></script> -->
      <script src="js/cat.js"></script>
      <!-- <script src="js/buttons.js"></script> -->

  </body>
</html>
