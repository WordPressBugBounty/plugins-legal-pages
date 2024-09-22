<?php
$adl_legal_pages = (!empty($args['adl_legal_pages'])) ? $args['adl_legal_pages'] : null;
$adl_lp_templates = (!empty($args['adl_lp_templates'])) ? $args['adl_lp_templates'] : null;

$homeTabData = array_merge(get_option('adl_lp_general'), get_option('adl_lp_social'), get_option('adl_lp_misc'));
?>
<section style="background:#efefe9;">
    <div class="container-fluid" style="margin: 0; padding: 0">
        <div class="row">
            <div class="board">
                <!--board-inner starts ::: Tab Menu-->
                <div class="board-inner">
                    <ul class="nav nav-tabs" id="myTab">
                        <div class="liner"></div>
                        <li class="active">
                            <a href="#home" data-toggle="tab">
                              <span class="round-tabs one">
                                      <i class="glyphicon glyphicon-cog"></i>
                              </span>
                            </a>
                            <p class="lp_tablabel">General Settings</p>

                        </li>

                        <li><a href="#createLegalPage" data-toggle="tab">
                             <span class="round-tabs two">
                                 <i class="glyphicon glyphicon-plus-sign"></i>
                             </span>
                            </a>
                            <p class="lp_tablabel">Add New Page</p>

                        </li>
                        <li><a href="#allPages" data-toggle="tab" >
                             <span class="round-tabs three">
                                  <i class="glyphicon glyphicon-list"></i>
                             </span>
                            </a>
                            <p class="lp_tablabel">All Legal Pages</p>
                        </li>

                        <li><a href="#editTemplates" data-toggle="tab" >
                             <span class="round-tabs four">
                                  <i class="glyphicon glyphicon-edit"></i>
                             </span>
                            </a>
                            <p class="lp_tablabel">All Legal Page Templates</p>
                        </li>

                        <li><a href="#Support" data-toggle="tab">
                                 <span class="round-tabs five">
                                      <i class="glyphicon glyphicon-send"></i>
                                 </span>
                             </a>
                            <p class="lp_tablabel">Get Support</p>

                        </li>

                    </ul>
                </div>   <!--Ends board-inner-->


                <div class="tab-content">

                    <div class="tab-pane fade in active" id="home">
                        <?php $ADL_LP->loadView('settings/tab-content/home', $homeTabData); ?>
                    </div>  <!--ends .tab-pane   #home-->

                    <div class="tab-pane fade" id="createLegalPage">
                        <?php $ADL_LP->loadView('settings/tab-content/create-page'); ?>
                    </div>  <!--ends .tab-pane   #createLegalPage-->


                    <div class="tab-pane fade" id="allPages">
                        <h3 class="head text-center">All Legal Pages</h3>
                        <?php $ADL_LP->loadView('settings/tab-content/list-legal-pages', $adl_legal_pages); ?>

                    </div> <!--ends .tab-pane   #allPages-->

                    <div class="tab-pane fade" id="editTemplates">
                        <h3 class="head text-center">Edit or Delete Page Template</h3>
                        <?php $ADL_LP->loadView('settings/tab-content/create-edit-templates-for-tab', $adl_lp_templates); ?>

                    </div> <!--ends .tab-pane   #editTemplates-->

                    <div class="tab-pane fade" id="Support">
                        <div class="text-center">
                            <i class="img-intro icon-checkmark-circle"></i>
                        </div>
                        <div
                            class="container">
                            <div class="row">
                                <div class="col-md-12 text-center" >
                                    <h3 style="margin-top: 50px;">====== Support Forum ======</h3>
                                    <p><p>If you need any helps, please don't hesitate to post it on <a href="https://wordpress.org/support/plugin/legal-pages" target="_blank">WordPress.org Support Forum</a> or <a href="https://wpwax.com/contact" target="_blank">wpwax.com Support Forum</a>.</p></p><br />
                                    <h3>====== More Features ======</h3>
                                    <p>Upgrading to the <a href="https://wpwax.com/product/adl-legal-pages-pro" target="_blank">Pro Version</a> would unlock more amazing features of the plugin.</p>
                                </div>
                            </div>
                        </div>
                    </div> <!--ends .tab-pane   #Support-->

                    <div class="clearfix"></div>

                </div> <!--Ends tab-content -->

            </div> <!--    end .board -->

        </div>
    </div>
</section>
