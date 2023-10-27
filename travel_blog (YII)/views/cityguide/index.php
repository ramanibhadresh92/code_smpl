<?php  
use frontend\assets\AppAsset;
use backend\models\Googlekey;
 
$baseUrl = AppAsset::register($this)->baseUrl;
$session = Yii::$app->session; 
$email = $session->get('email'); 
$status = $session->get('status');
$fullname = $session->get('fullname'); 
$user_id = (string)$session->get('user_id');  
$this->title = 'City Guide';
$data = array('id' => (string)$user_id, 'email'=> $email, 'fullname' => $fullname);
$GApiKeyL = $GApiKeyP = Googlekey::getkey();
?>

<script src="<?=$baseUrl?>/js/chart.js"></script>
    <div class="page-wrapper  mainfeed-page"> 
        <div class="header-section">
            <?php include('../views/layouts/header.php'); ?>
        </div>
        <?php include('../views/layouts/menu.php'); ?>
        <div class="floating-icon">
		   <div class="scrollup-btnbox anim-side btnbox scrollup-float">
		      <div class="scrollup-button float-icon">
		         <span class="icon-holder ispan">
		            <i class="mdi mdi-arrow-up-bold-circle"></i>
		         </span>
		      </div>
		   </div>
		</div>
		<div class="clear"></div>
		<div>
			<?php include('../views/layouts/leftmenu.php'); ?>
			<div class="fixed-layout">
				<div class="main-content main-page places-page pb-0 m-t-50">
			        <div class="combined-column wide-open main-page full-page">
		                <div class="tablist sub-tabs">
		                    <ul class="tabs tabs-fixed-width text-menu left tabsnew">
		                        <li class="tab"><a tabname="Wall" href="#places-all"></a></li>
		                    </ul>
		                </div>
			            <div class="places-content places-all">
			                <div class="container cshfsiput cshfsi">
			                    <div class="places-column cshfsiput cshfsi m-top">
			                        <div class="tab-content">
			                            <div id="places-cityguide" class="placescityguide-content subtab bottom_tabs">
			                                <div class="row cshfsiput cshfsi">
			                                    <?php include('leftbox.php'); ?>
			                                    <div class="postBox">
			                                        <div class="content-box ">
			                                            <div class="new-post base-newpost cshfsiput cshfsi compose_discus">
			                                                <div class="npost-content">
			                                                    <div class="post-mcontent">
			                                                        <i class="mdi mdi-pencil-box-outline main-icon"></i>
			                                                        <div class="desc">
			                                                            <div class="input-field comments_box">
			                                                                <p>Cityguide for Japan</p>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                            <div class="cbox-desc nm-postlist post-list cshfsiput cshfsi">
			                                                <div class="post-holder bborder tippost-holder">
			                                                    <div class="post-topbar">
			                                                        <div class="post-userinfo">
			                                                            <div class="img-holder">
			                                                                <div id="profiletip-1" class="profiletipholder">
			                                                                    <span class="profile-tooltip">
			                                                                    <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                    </span>
			                                                                    <span class="profiletooltip_content">
			                                                                        <div class="profile-tip">
			                                                                            <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                            <div class="profile-tip-avatar">
			                                                                                <a href="javascript:void(0)">
			                                                                                <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                </a>
			                                                                            </div>
			                                                                            <div class="profile-tip-info">
			                                                                                <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                <div class="cover-headline">
			                                                                                    <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                    Web Designer, Cricketer
			                                                                                </div>
			                                                                                <div class="profiletip-bio">
			                                                                                    <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                    Lives in : <span>Gariyadhar</span>
			                                                                                </div>
			                                                                                <div class="profiletip-bio">
			                                                                                    <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                    Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="profile-tip-divider"></div>
			                                                                            <div class="profile-tip-btn">
			                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                            </div>
			                                                                        </div>
			                                                                    </span>
			                                                                </div>
			                                                            </div>
			                                                            <div class="desc-holder">
			                                                                <a href="javascript:void(0)">Adel Hasanat</a> tip for <a class="sub-link" href="javascript:void(0)">Japan</a>
			                                                                <span class="timestamp">August 31 at 08:45 pm<span class="glyphicon glyphicon-globe"></span></span>
			                                                            </div>
			                                                        </div>
			                                                        <div class="settings-icon">
			                                                            <div class="dropdown">
			                                                                <a class="dropdown-button" href="javascript:void(0)" data-activates="dropdown-editdisc">
			                                                                <i class="zmdi zmdi-more zmdi-hc-2x"></i>
			                                                                </a>
			                                                                <ul class="dropdown-content" id="dropdown-editdisc">
			                                                                    <li>
			                                                                        <a href="javascript:void(0)" class="edit_discus">Edit Discussion</a>
			                                                                    </li>
			                                                                </ul>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                    <div class="post-content">
			                                                        <div class="post-details">
			                                                            <div class="post-title">Random Title</div>
			                                                            <div class="post-desc">
			                                                                <div class="para-section">
			                                                                    <div class="para">
			                                                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.</p>
			                                                                    </div>
			                                                                    <a href="javascript:void(0)" onclick="showAllContent(this)">Read More</a>
			                                                                </div>
			                                                            </div>
			                                                        </div>
			                                                        <div class="post-img-holder">
			                                                            <div class="lgt-gallery post-img two-img lgt-gallery-photo dis-none">
			                                                                <a href="<?=$baseUrl?>/images/post-img2.jpg" data-size="1600x1600" data-med="<?=$baseUrl?>/images/post-img2.jpg" data-med-size="1024x1024" data-author="Folkert Gorter" class="pimg-holder himg-box">
			                                                                <img class="himg" src="<?=$baseUrl?>/images/post-img2.jpg" alt="" />
			                                                                </a>
			                                                                <a href="<?=$baseUrl?>/images/post-img3.jpg" data-size="1600x1068" data-med="<?=$baseUrl?>/images/post-img3.jpg" data-med-size="1024x683" data-author="Samuel Rohl" class="pimg-holder vimg-box">
			                                                                <img class="himg" src="<?=$baseUrl?>/images/post-img3.jpg" alt="" />
			                                                                </a>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                    <div class="clear"></div>
			                                                    <div class="post-data">
			                                                        <div class="post-actions">
			                                                            <div class="right like-tooltip">
			                                                                <a href="javascript:void(0)" class="pa-like" data-title="User Name"><i class="zmdi zmdi-thumb-up"></i></a>
			                                                                <span class="lcount">4</span>
			                                                                <a href="javascript:void(0)" class="pa-comment"><i class="zmdi zmdi-comment"></i></a>
			                                                                <span class="comment-lcount">4</span>
			                                                            </div>
			                                                        </div>
			                                                        <div class="comments-section panel">
			                                                            <div class="comments-area">
			                                                                <div class="post-more">
			                                                                    <a href="javascript:void(0)" class="view-morec">View more comments</a>
			                                                                    <span class="total-comments">3 of 7</span>
			                                                                </div>
			                                                                <div class="post-comments">
			                                                                    <div class="pcomments">
			                                                                        <div class="pcomment-earlier">
			                                                                            <div class="pcomment-holder">
			                                                                                <div class="pcomment main-comment">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-1" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdeleteDisc1">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdeleteDisc1">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="clear"></div>
			                                                                                <div class="comment-reply-holder comment-addreply">
			                                                                                    <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                        <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                        <div class="desc-holder">
			                                                                                            <div class="sliding-middle-custom anim-area">
			                                                                                                <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="pcomment-holder">
			                                                                                <div class="pcomment main-comment">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-2" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdeleteDisc2">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdeleteDisc2"">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="clear"></div>
			                                                                                <div class="comment-reply-holder comment-addreply">
			                                                                                    <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                        <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                        <div class="desc-holder">
			                                                                                            <div class="sliding-middle-custom anim-area">
			                                                                                                <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                        </div>
			                                                                        <div class="pcomment-holder has-comments">
			                                                                            <div class="pcomment main-comment">
			                                                                                <div class="img-holder">
			                                                                                    <div id="commentptip-3" class="profiletipholder">
			                                                                                        <span class="profile-tooltip">
			                                                                                        <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                        </span>
			                                                                                        <span class="profiletooltip_content">
			                                                                                            <div class="profile-tip">
			                                                                                                <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                <div class="profile-tip-avatar">
			                                                                                                    <a href="javascript:void(0)">
			                                                                                                    <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                    </a>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-info">
			                                                                                                    <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                    <div class="cover-headline">
			                                                                                                        <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                        Web Designer, Cricketer
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                        Lives in : <span>Gariyadhar</span>
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                        Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-divider"></div>
			                                                                                                <div class="profile-tip-btn">
			                                                                                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </span>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="desc-holder">
			                                                                                    <div class="normal-mode">
			                                                                                        <div class="desc">
			                                                                                            <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                        </div>
			                                                                                        <div class="comment-stuff">
			                                                                                            <div class="more-opt">
			                                                                                                <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                    <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdeleteDisc3">
			                                                                                                    <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                    </a>
			                                                                                                    <ul class="dropdown-content" id="dropdown-editdeleteDisc3">
			                                                                                                        <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                        <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                    </ul>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                            <div class="less-opt">
			                                                                                                <div class="timestamp">8h</div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="edit-mode">
			                                                                                        <div class="desc">
			                                                                                            <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="clear"></div>
			                                                                            <div class="comment-reply-holder">
			                                                                                <div class="pcomment comment-reply">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-5" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdeleteDisc4">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdeleteDisc4">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="pcomment comment-reply">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-6" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit...</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdeleteDisc5">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdeleteDisc5">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="comment-reply-holder comment-addreply">
			                                                                                <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                    <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="sliding-middle-custom anim-area">
			                                                                                            <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                        </div>
			                                                                        <div class="pcomment-holder">
			                                                                            <div class="pcomment main-comment">
			                                                                                <div class="img-holder">
			                                                                                    <div id="commentptip-4" class="profiletipholder">
			                                                                                        <span class="profile-tooltip">
			                                                                                        <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                        </span>
			                                                                                        <span class="profiletooltip_content">
			                                                                                            <div class="profile-tip">
			                                                                                                <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                <div class="profile-tip-avatar">
			                                                                                                    <a href="javascript:void(0)">
			                                                                                                    <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                    </a>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-info">
			                                                                                                    <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                    <div class="cover-headline">
			                                                                                                        <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                        Web Designer, Cricketer
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                        Lives in : <span>Gariyadhar</span>
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                        Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-divider"></div>
			                                                                                                <div class="profile-tip-btn">
			                                                                                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </span>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="desc-holder">
			                                                                                    <div class="normal-mode">
			                                                                                        <div class="desc">
			                                                                                            <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                        </div>
			                                                                                        <div class="comment-stuff">
			                                                                                            <div class="more-opt">
			                                                                                                <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                    <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdeleteDisc6">
			                                                                                                    <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                    </a>
			                                                                                                    <ul class="dropdown-content" id="dropdown-editdeleteDisc6">
			                                                                                                        <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                        <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                    </ul>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                            <div class="less-opt">
			                                                                                                <div class="timestamp">8h</div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="edit-mode">
			                                                                                        <div class="desc">
			                                                                                            <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="clear"></div>
			                                                                            <div class="comment-reply-holder comment-addreply">
			                                                                                <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                    <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="sliding-middle-custom anim-area">
			                                                                                            <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                        </div>
			                                                                    </div>
			                                                                    <div class="addnew-comment valign-wrapper">
			                                                                        <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                        <div class="desc-holder">
			                                                                            <div class="sliding-middle-custom anim-area">
			                                                                                <textarea data-adaptheight class="materialize-textarea mb0 md_textarea item_tagline data-adaptheight">Write a comment</textarea>
			                                                                            </div>
			                                                                        </div>
			                                                                    </div>
			                                                                </div>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                </div>
			                                                <div class="post-holder bborder tippost-holder">
			                                                    <div class="post-topbar">
			                                                        <div class="post-userinfo">
			                                                            <div class="img-holder">
			                                                                <div id="profiletip-1" class="profiletipholder">
			                                                                    <span class="profile-tooltip">
			                                                                    <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                    </span>
			                                                                    <span class="profiletooltip_content">
			                                                                        <div class="profile-tip">
			                                                                            <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                            <div class="profile-tip-avatar">
			                                                                                <a href="javascript:void(0)">
			                                                                                <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                </a>
			                                                                            </div>
			                                                                            <div class="profile-tip-info">
			                                                                                <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                <div class="cover-headline">
			                                                                                    <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                    Web Designer, Cricketer
			                                                                                </div>
			                                                                                <div class="profiletip-bio">
			                                                                                    <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                    Lives in : <span>Gariyadhar</span>
			                                                                                </div>
			                                                                                <div class="profiletip-bio">
			                                                                                    <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                    Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="profile-tip-divider"></div>
			                                                                            <div class="profile-tip-btn">
			                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                            </div>
			                                                                        </div>
			                                                                    </span>
			                                                                </div>
			                                                            </div>
			                                                            <div class="desc-holder">
			                                                                <a href="javascript:void(0)">Nimish Parekh</a> tip for <a class="sub-link" href="javascript:void(0)">Japan</a>
			                                                                <span class="timestamp">August 31 at 08:45 pm<span class="glyphicon glyphicon-globe"></span></span>
			                                                            </div>
			                                                        </div>
			                                                        <div class="settings-icon">
			                                                            <div class="dropdown">
			                                                                <a class="dropdown-button" href="javascript:void(0)" data-activates="dropdown-editdisc2">
			                                                                <i class="zmdi zmdi-more zmdi-hc-2x"></i>
			                                                                </a>
			                                                                <ul class="dropdown-content" id="dropdown-editdisc2">
			                                                                    <li>
			                                                                        <a href="javascript:void(0)" class="edit_discus">Edit Discussion</a>
			                                                                    </li>
			                                                                </ul>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                    <div class="post-content">
			                                                        <div class="post-details">
			                                                            <div class="post-desc">
			                                                                <div class="para-section">
			                                                                    <div class="para">
			                                                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.<br />Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sed varius risus. Duis rhoncus eros et pellentesque imperdiet.</p>
			                                                                    </div>
			                                                                    <a href="javascript:void(0)" onclick="showAllContent(this)">Read More</a>
			                                                                </div>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                    <div class="clear"></div>
			                                                    <div class="post-data">
			                                                        <div class="post-actions">
			                                                            <div class="right like-tooltip">
			                                                                <a href="javascript:void(0)" class="pa-like" data-title="User Name"><i class="zmdi zmdi-thumb-up"></i></a>
			                                                                <span class="lcount">4</span>
			                                                                <a href="javascript:void(0)" class="pa-comment"><i class="zmdi zmdi-comment"></i></a>
			                                                                <span class="comment-lcount">4</span>
			                                                            </div>
			                                                        </div>
			                                                        <div class="comments-section panel">
			                                                            <div class="comments-area">
			                                                                <div class="post-more">
			                                                                    <a href="javascript:void(0)" class="view-morec">View more comments</a>
			                                                                    <span class="total-comments">3 of 7</span>
			                                                                </div>
			                                                                <div class="post-comments">
			                                                                    <div class="pcomments">
			                                                                        <div class="pcomment-earlier">
			                                                                            <div class="pcomment-holder">
			                                                                                <div class="pcomment main-comment">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-1" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdelete13">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdelete13">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="clear"></div>
			                                                                                <div class="comment-reply-holder comment-addreply">
			                                                                                    <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                        <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                        <div class="desc-holder">
			                                                                                            <div class="sliding-middle-custom anim-area">
			                                                                                                <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="pcomment-holder">
			                                                                                <div class="pcomment main-comment">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-2" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdelete12">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdelete12">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="clear"></div>
			                                                                                <div class="comment-reply-holder comment-addreply">
			                                                                                    <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                        <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                        <div class="desc-holder">
			                                                                                            <div class="sliding-middle-custom anim-area">
			                                                                                                <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                        </div>
			                                                                        <div class="pcomment-holder has-comments">
			                                                                            <div class="pcomment main-comment">
			                                                                                <div class="img-holder">
			                                                                                    <div id="commentptip-3" class="profiletipholder">
			                                                                                        <span class="profile-tooltip">
			                                                                                        <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                        </span>
			                                                                                        <span class="profiletooltip_content">
			                                                                                            <div class="profile-tip">
			                                                                                                <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                <div class="profile-tip-avatar">
			                                                                                                    <a href="javascript:void(0)">
			                                                                                                    <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                    </a>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-info">
			                                                                                                    <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                    <div class="cover-headline">
			                                                                                                        <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                        Web Designer, Cricketer
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                        Lives in : <span>Gariyadhar</span>
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                        Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-divider"></div>
			                                                                                                <div class="profile-tip-btn">
			                                                                                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </span>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="desc-holder">
			                                                                                    <div class="normal-mode">
			                                                                                        <div class="desc">
			                                                                                            <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                        </div>
			                                                                                        <div class="comment-stuff">
			                                                                                            <div class="more-opt">
			                                                                                                <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                    <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdelete11">
			                                                                                                    <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                    </a>
			                                                                                                    <ul class="dropdown-content" id="dropdown-editdelete11">
			                                                                                                        <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                        <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                    </ul>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                            <div class="less-opt">
			                                                                                                <div class="timestamp">8h</div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="edit-mode">
			                                                                                        <div class="desc">
			                                                                                            <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="clear"></div>
			                                                                            <div class="comment-reply-holder">
			                                                                                <div class="pcomment comment-reply">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-5" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdelete10">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdelete10">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="pcomment comment-reply">
			                                                                                    <div class="img-holder">
			                                                                                        <div id="commentptip-6" class="profiletipholder">
			                                                                                            <span class="profile-tooltip">
			                                                                                            <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                            </span>
			                                                                                            <span class="profiletooltip_content">
			                                                                                                <div class="profile-tip">
			                                                                                                    <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                    <div class="profile-tip-avatar">
			                                                                                                        <a href="javascript:void(0)">
			                                                                                                        <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                        </a>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-info">
			                                                                                                        <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                        <div class="cover-headline">
			                                                                                                            <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                            Web Designer, Cricketer
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                            Lives in : <span>Gariyadhar</span>
			                                                                                                        </div>
			                                                                                                        <div class="profiletip-bio">
			                                                                                                            <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                            Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                        </div>
			                                                                                                    </div>
			                                                                                                    <div class="profile-tip-divider"></div>
			                                                                                                    <div class="profile-tip-btn">
			                                                                                                        <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                            </span>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="normal-mode">
			                                                                                            <div class="desc">
			                                                                                                <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit...</p>
			                                                                                            </div>
			                                                                                            <div class="comment-stuff">
			                                                                                                <div class="more-opt">
			                                                                                                    <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                    <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                    <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                        <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdelete9">
			                                                                                                        <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                        </a>
			                                                                                                        <ul class="dropdown-content" id="dropdown-editdelete9">
			                                                                                                            <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                            <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                        </ul>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="less-opt">
			                                                                                                    <div class="timestamp">8h</div>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                        <div class="edit-mode">
			                                                                                            <div class="desc">
			                                                                                                <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="comment-reply-holder comment-addreply">
			                                                                                <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                    <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="sliding-middle-custom anim-area">
			                                                                                            <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                        </div>
			                                                                        <div class="pcomment-holder">
			                                                                            <div class="pcomment main-comment">
			                                                                                <div class="img-holder">
			                                                                                    <div id="commentptip-4" class="profiletipholder">
			                                                                                        <span class="profile-tooltip">
			                                                                                        <img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" />
			                                                                                        </span>
			                                                                                        <span class="profiletooltip_content">
			                                                                                            <div class="profile-tip">
			                                                                                                <div class="profile-tip-cover"><img src="<?=$baseUrl?>/images/cover.jpg"></div>
			                                                                                                <div class="profile-tip-avatar">
			                                                                                                    <a href="javascript:void(0)">
			                                                                                                    <img alt="user-photo" class="img-responsive" src="<?=$baseUrl?>/images/demo-profile.jpg">
			                                                                                                    </a>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-info">
			                                                                                                    <div class="cover-username"><a href="javascript:void(0)">Adel Hasanat</a></div>
			                                                                                                    <div class="cover-headline">
			                                                                                                        <span class="ptip-icon"><i class="fa  fa-suitcase"></i></span>
			                                                                                                        Web Designer, Cricketer
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="mdi mdi-home"></i></span>
			                                                                                                        Lives in : <span>Gariyadhar</span>
			                                                                                                    </div>
			                                                                                                    <div class="profiletip-bio">
			                                                                                                        <span class="ptip-icon"><i class="zmdi zmdi-pin"></i></span>
			                                                                                                        Currently in : <span>Gariyadhar, Gujarat, India</span>
			                                                                                                    </div>
			                                                                                                </div>
			                                                                                                <div class="profile-tip-divider"></div>
			                                                                                                <div class="profile-tip-btn">
			                                                                                                    <a href="javascript:void(0)" class="btn btn-primary btn-sm"><i class="mdi mdi-eye"></i>View Profile</a>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                        </span>
			                                                                                    </div>
			                                                                                </div>
			                                                                                <div class="desc-holder">
			                                                                                    <div class="normal-mode">
			                                                                                        <div class="desc">
			                                                                                            <a href="javascript:void(0)" class="userlink">Adel Hasanat</a>
			                                                                                            <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</p>
			                                                                                        </div>
			                                                                                        <div class="comment-stuff">
			                                                                                            <div class="more-opt">
			                                                                                                <a href="javascript:void(0)" class="pa-like"><span>icon</span></a>
			                                                                                                <a href="javascript:void(0)" class="pa-reply reply-comment"><span>icon</span></a>
			                                                                                                <div class="dropdown dropdown-custom dropdown-xxsmall">
			                                                                                                    <a href="javascript:void(0)" class="dropdown-toggle dropdown-button" data-activates="dropdown-editdelete8">
			                                                                                                    <i class="zmdi zmdi-hc-2x zmdi-more-vert"></i>
			                                                                                                    </a>
			                                                                                                    <ul class="dropdown-content" id="dropdown-editdelete8">
			                                                                                                        <li><a href="javascript:void(0)" class="edit-comment">Edit</a></li>
			                                                                                                        <li><a href="javascript:void(0)" class="delete-comment">Delete</a></li>
			                                                                                                    </ul>
			                                                                                                </div>
			                                                                                            </div>
			                                                                                            <div class="less-opt">
			                                                                                                <div class="timestamp">8h</div>
			                                                                                            </div>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                    <div class="edit-mode">
			                                                                                        <div class="desc">
			                                                                                            <textarea class="editcomment-tt materialize-textarea mb0 md_textarea item_tagline">Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh.</textarea>
			                                                                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm editcomment-cancel waves-effect waves-light">Cancel</a>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                            <div class="clear"></div>
			                                                                            <div class="comment-reply-holder comment-addreply">
			                                                                                <div class="addnew-comment valign-wrapper comment-reply">
			                                                                                    <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                                    <div class="desc-holder">
			                                                                                        <div class="sliding-middle-custom anim-area">
			                                                                                            <textarea class="materialize-textarea mb0 md_textarea item_tagline">Write a reply...</textarea>
			                                                                                        </div>
			                                                                                    </div>
			                                                                                </div>
			                                                                            </div>
			                                                                        </div>
			                                                                    </div>
			                                                                    <div class="addnew-comment valign-wrapper">
			                                                                        <div class="img-holder"><a href="javascript:void(0)"><img class="circle" src="<?=$baseUrl?>/images/demo-profile.jpg" /></a></div>
			                                                                        <div class="desc-holder">
			                                                                            <div class="sliding-middle-custom anim-area">
			                                                                                <textarea data-adaptheight class="materialize-textarea mb0 md_textarea item_tagline data-adaptheight">Write a comment</textarea>
			                                                                            </div>
			                                                                        </div>
			                                                                    </div>
			                                                                </div>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                        </div>
			                                        <div class="new-post-mobile clear disscu_show">
			                                            <a href="javascript:void(0)" class="popup-window compose_discus" ><i class="mdi mdi-pencil"></i></a>
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
			                        </div>
			                    </div>
			                    <div class="wallcontent-column cshfsiput cshfsi">
			                        <div class="sidebar-stuff">
			                            <div class="content-box bshadow rightmap">
			                                <div class="cbox-desc">
			                                    <div class="placeintro-side width-100">
			                                        <h5>Japan</h5>
			                                        <div class="map-holder">
			                                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3110.3465133386144!2d-9.167423685010494!3d38.77868997958898!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd193295d5b45545%3A0x3f9e7b6a5f00e12c!2sPerta!5e0!3m2!1sen!2sin!4v1481089901870" width="600" height="450" frameborder="0" allowfullscreen></iframe>
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="content-box bshadow">
			                                <div class="cbox-title nborder">
			                                    <i class="mdi mdi-airplane"></i>
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-travellers')">Travellers</a>
			                                </div>
			                                <div class="cbox-desc">
			                                    <div class="connect-list grid-list">
			                                        <div class="row">
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder online-img"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                    <span class="online-mark"><i class="zmdi zmdi-check"></i></span>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder online-img"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                    <span class="online-mark"><i class="zmdi zmdi-check"></i></span>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                    <span class="online-mark"><i class="zmdi zmdi-check"></i></span>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="content-box bshadow">
			                                <div class="cbox-title nborder">
			                                    <i class="mdi mdi-map"></i>
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-locals')">Local</a>
			                                </div>
			                                <div class="cbox-desc">
			                                    <div class="connect-list grid-list">
			                                        <div class="row">
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                    <span class="online-mark"><i class="zmdi zmdi-check"></i></span>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                    <span class="online-mark"><i class="zmdi zmdi-check"></i></span>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                    <span class="online-mark"><i class="zmdi zmdi-check"></i></span>
			                                                </div>
			                                            </div>
			                                            <div class="grid-box">
			                                                <div class="connect-box">
			                                                    <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                    <div class="descholder">
			                                                        <a href="javascript:void(0)">
			                                                        <span class="userlink">John Doe</span>
			                                                        <span class="info">2 posts</span>
			                                                        </a>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="content-box bshadow">
			                                <div class="cbox-title nborder">
			                                    <i class="zmdi zmdi-view-list-alt zmdi-hc-lg"></i>
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-reviews')">Review</a>
			                                </div>
			                                <div class="cbox-desc">
			                                    <div class="reviews-summery">
			                                        <div class="reviews-add">
			                                            <div class="stars-holder">
			                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                            </div>
			                                            <p>What do you think about this page?</p>
			                                        </div>
			                                        <div class="reviews-people">
			                                            <ul>
			                                                <li>
			                                                    <div class="reviewpeople-box">
			                                                        <div class="imgholder"><img src="<?=$baseUrl?>/images/people-3.png" /></div>
			                                                        <div class="descholder">
			                                                            <h6>Kelly Mark <span>about 2 weeks ago</span></h6>
			                                                            <div class="stars-holder">
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                                <p>Very nice hotel</p>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                </li>
			                                                <li>
			                                                    <div class="reviewpeople-box">
			                                                        <div class="imgholder"><img src="<?=$baseUrl?>/images/people-2.png" /></div>
			                                                        <div class="descholder">
			                                                            <h6>John Davior <span>about 8 months ago</span></h6>
			                                                            <div class="stars-holder">
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                                <p>Nice hotel</p>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                </li>
			                                                <li>
			                                                    <div class="reviewpeople-box">
			                                                        <div class="imgholder"><img src="<?=$baseUrl?>/images/people-1.png" /></div>
			                                                        <div class="descholder">
			                                                            <h6>Joe Doe <span>about 11 months ago</span></h6>
			                                                            <div class="stars-holder">
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/filled-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                                <img src="<?=$baseUrl?>/images/blank-star.png" />
			                                                                <p>Very nice hotel</p>
			                                                            </div>
			                                                        </div>
			                                                    </div>
			                                                </li>
			                                            </ul>
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="places-travad">
			                                <a href="javascript:void(0)">
			                                <img src="<?=$baseUrl?>/images/booking-ad.jpg" />
			                                </a>
			                            </div>
			                            <div class="content-box bshadow">
			                                <div class="cbox-title nborder">
			                                    <i class="mdi mdi-office-building"></i>
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-lodge')">Hotels in Japan</a>
			                                </div>
			                                <div class="cbox-desc">
			                                    <div class="places-dealsad">
			                                        <ul>
			                                            <li>
			                                                <div class="placebox">
			                                                    <a href="javascript:void(0)">
			                                                        <div class="imgholder himg-box">
			                                                            <img src="<?=$baseUrl?>/images/lodge-img-1.jpg" class="himg imgfix" />
			                                                            <div class="overlay"></div>
			                                                        </div>
			                                                        <div class="descholder">
			                                                            <h5>Moeavenpick Resort Japan</h5>
			                                                            <span class="ratings">
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star"></i>
			                                                            <i class="mdi mdi-star"></i>
			                                                            <label>45 Reviews</label>
			                                                            </span>
			                                                            <div class="tags">
			                                                                <span>Luxury</span>
			                                                                <span>Families</span>
			                                                            </div>
			                                                        </div>
			                                                    </a>
			                                                </div>
			                                            </li>
			                                            <li>
			                                                <div class="placebox">
			                                                    <a href="javascript:void(0)">
			                                                        <div class="imgholder himg-box">
			                                                            <img src="<?=$baseUrl?>/images/lodge-img-2.jpg" class="himg" />
			                                                            <div class="overlay"></div>
			                                                        </div>
			                                                        <div class="descholder">
			                                                            <h5>Japan Moon Hotel</h5>
			                                                            <span class="ratings">
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star"></i>
			                                                            <label>20 Reviews</label>
			                                                            </span>
			                                                            <div class="tags">
			                                                                <span>Budget</span>
			                                                            </div>
			                                                        </div>
			                                                    </a>
			                                                </div>
			                                            </li>
			                                            <li>
			                                                <div class="placebox">
			                                                    <a href="javascript:void(0)">
			                                                        <div class="imgholder himg-box">
			                                                            <img src="<?=$baseUrl?>/images/lodge-img-3.jpg" class="himg" />
			                                                            <div class="overlay"></div>
			                                                        </div>
			                                                        <div class="descholder">
			                                                            <h5>Marriott Japan Hotel</h5>
			                                                            <span class="ratings">
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <i class="mdi mdi-star active"></i>
			                                                            <label>65 Reviews</label>
			                                                            </span>
			                                                            <div class="tags">
			                                                                <span>Luxury</span>
			                                                                <span>Business</span>
			                                                            </div>
			                                                        </div>
			                                                    </a>
			                                                </div>
			                                            </li>
			                                        </ul>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="content-box bshadow">
			                                <div class="cbox-title nborder">
			                                    <i class="zmdi zmdi-help"></i>
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-ask')">Recent Questions</a>
			                                </div>
			                                <div class="cbox-desc">
			                                    <div class="question-holder">
			                                        <ul>
			                                            <li>
			                                                <img src="<?=$baseUrl?>/images/people-1.png" />
			                                                <h6>Adel Hasanat</h6>
			                                                <p>
			                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard?
			                                                </p>
			                                            </li>
			                                            <li>
			                                                <img src="<?=$baseUrl?>/images/people-2.png" />
			                                                <h6>Adel Hasanat</h6>
			                                                <p>
			                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard?
			                                                </p>
			                                            </li>
			                                        </ul>
			                                        <div class="btn-holder">
			                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm waves-effect waves-light">View all questions</a>
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="content-box bshadow">
			                                <div class="cbox-title nborder">
			                                    <i class="mdi mdi-star"></i>
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-reviews')">Recently Reviews</a>
			                                </div>
			                                <div class="cbox-desc">
			                                    <div class="question-holder">
			                                        <ul>
			                                            <li>
			                                                <img src="<?=$baseUrl?>/images/people-1.png" />
			                                                <h6>Adel Hasanat</h6>
			                                                <p>
			                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard?
			                                                </p>
			                                            </li>
			                                        </ul>
			                                        <div class="btn-holder">
			                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm waves-effect waves-light">More Hotel Reviews</a>
			                                        </div>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="content-box bshadow">
			                                <div class="cbox-title nborder">
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-tip')">Tips for Japan</a>
			                                </div>
			                                <div class="cbox-desc nsp">
			                                    <div class="question-holder tips-holder">
			                                        <ul>
			                                            <li>
			                                                <img src="<?=$baseUrl?>/images/people-1.png" />
			                                                <h6><span>Tip by</span> Adel Hasanat</h6>
			                                                <p>
			                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard.
			                                                    <a href="javascript:void(0)" class="arrow-more"><i class="mdi mdi-arrow-right-bold-circle-outline"></i></a>
			                                                </p>
			                                            </li>
			                                            <li>
			                                                <img src="<?=$baseUrl?>/images/people-2.png" />
			                                                <h6><span>Tip by</span> Adel Hasanat</h6>
			                                                <p>
			                                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard.Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard.
			                                                    <a href="javascript:void(0)" class="arrow-more"><i class="mdi mdi-arrow-right-bold-circle-outline"></i></a>
			                                                </p>
			                                            </li>
			                                        </ul>
			                                    </div>
			                                </div>
			                            </div>
			                            <div class="content-box bshadow">
			                                <img src="<?=$baseUrl?>/images/side-japan.jpg" class="fullimg" />
			                                <div class="cbox-title nborder">
			                                    <a href="javascript:void(0)" onclick="openDirectTab('places-all')">Explore Japan</a>
			                                </div>
			                                <div class="cbox-desc">
			                                    <div class="explore-box">
			                                        <ul class="explore-list">
			                                            <li><a href="javascript:void(0)" onclick="openDirectTab('places-lodge')"><i class="mdi mdi-menu-right"></i>Hotels</a></li>
			                                            <li><a href="javascript:void(0)" onclick="openDirectTab('places-todo')"><i class="mdi mdi-menu-right"></i>Attractions</a></li>
			                                            <li><a href="javascript:void(0)" onclick="openDirectTab('places-dine')"><i class="mdi mdi-menu-right"></i>Restaurants</a></li>
			                                            <li><a href="javascript:void(0)" onclick="openDirectTab('places-all')"><i class="mdi mdi-menu-right"></i>Vacation Rentals</a></li>
			                                        </ul>
			                                    </div>
			                                </div>
			                            </div>
			                        </div>
			                    </div>
			                </div>
			            </div>
			        </div>
			    </div>
			</div>
		</div>
    </div>  
 
	<input type="hidden" name="pagename" id="pagename" value="feed" />
	<input type="hidden" name="tlid" id="tlid" value="<?=(string)$user_id?>" />
	
    <div id="compose_tool_box" class="modal compose_tool_box post-popup custom_modal main_modal">
    </div> 
	 
	<div id="composeeditpostmodal" class="modal compose_tool_box edit_post_modal post-popup main_modal custom_modal compose_edit_modal">
    </div>
	
	<div id="sharepostmodal" class="modal sharepost_modal post-popup main_modal custom_modal">
	</div>
	
	<!-- Post detail modal -->
	<div id="postopenmodal" class="modal modal_main compose_tool_box custom_modal postopenmodal_main postopenmodal_new">	
	</div>
	
	<!--post comment modal for xs view-->
	<div id="comment_modal_xs" class="modal comment_modal_xs">
	</div>  
    <div id="compose_mapmodal" class="map_modalUniq modal map_modal compose_inner_modal modalxii_level1">
		<?php include('../views/layouts/mapmodal.php'); ?>
	</div>
    <?php include('../views/layouts/addpersonmodal.php'); ?>

    <?php include('../views/layouts/custom_modal.php'); ?>
    <?php include('../views/layouts/editphotomadol.php'); ?> 
    
   
   	<div id="upload-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="edit-gallery-popup" class="modal tbpost_modal custom_modal split-page main_modal cust-pop dicrease-popup-compose upload-gallery-popup"></div>

	<div id="userwall_tagged_users" class="modal modalxii_level1">
		<div class="content_header">
			<button class="close_span waves-effect">
				<i class="mdi mdi-close mdi-20px"></i>
			</button>
			<p class="selected_photo_text"></p>
			<a href="javascript:void(0)" class="chk_person_done_new done_btn focoutTRV03 action_btn">Done</a>
		</div>
		<nav class="search_for_tag">
			<div class="nav-wrapper">
			  <form>
			    <div class="input-field">
			      <input id="tagged_users_search_box" class="search_box" type="search" required="">
			        <label class="label-icon" for="tagged_users_search_box">
			          <i class="zmdi zmdi-search mdi-22px"></i>
			        </label>
			      </div>
			  </form>
			</div>
		</nav>
		<div class="person_box"></div>
	</div>
	<script>
var data1 = '';
var place = "<?php echo (string)$place?>";
var placetitle = "<?php echo (string)$placetitle?>";
var placefirst = "<?php echo (string)$placefirst?>";
var baseUrl = "<?php echo (string)$baseUrl; ?>";
var lat = "<?php echo $lat; ?>";
var lng = "<?php echo $lng; ?>";
</script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?=$GApiKeyL?>&libraries=places&callback=initAutocomplete"></script>

    <?php include('../views/layouts/commonjs.php'); ?>
    
    <script src="<?=$baseUrl?>/js/post.js"></script>
    <script type="text/javascript">
		$(document).ready(function() {
			justifiedGalleryinitialize();
    		lightGalleryinitialize();
		});

		/* Noor JS */
$(document).ready(function(){ 
$w = $(window).width();
if ( $w > 739) {      
$(".places-tabs .sub-tabs li a").click(function(){
   $("body").removeClass("remove_scroller");
}); 
$(".tabs.icon-menu.tabsnew li a").click(function(){
   $("body").removeClass("remove_scroller");
}); 
$(".mbl-tabnav").click(function(){
   $("body").removeClass("remove_scroller");
}); 
$(".clicable.viewall-link").click(function(){
   $("body").removeClass("remove_scroller");
}); 
} else {
$(".places-tabs .sub-tabs li a").click(function(){
   $("body").addClass("remove_scroller");
}); 
$(".clicable.viewall-link").click(function(){
   $("body").addClass("remove_scroller");
});         
$(".tabs.icon-menu.tabsnew li a").click(function(){
   $("body").addClass("remove_scroller");
}); 
$(".mbl-tabnav").click(function(){
   $("body").removeClass("remove_scroller");
});
}

$(".header-icon-tabs .tabsnew .tab a").click(function(){
$(".bottom_tabs").hide();
});

$(".places-tabs .tab a").click(function(){
$(".top_tabs").hide();
});



// footer work for places home page only
$('.footer-section').css('left', '0');
$w = $(window).width();
if($w <= 768) {
$('.main-footer').css({
   'width': '100%',
   'left': '0'
});
} else {
var $_I = $('.places-content.places-all').width();
var $__I = $('.places-content.places-all').find('.container').width();

var $half = parseInt($_I) - parseInt($__I);
$half = parseInt($half) / 2;

$('.main-footer').css({
   'width': $_I+'px',
   'left': '-'+$half+'px'
});
}
});

$(window).resize(function() {


// footer work for places home page only
if($('#places-all').hasClass('active')) {
$('.footer-section').css('left', '0');
$w = $(window).width();
if($w <= 768) {
   $('.main-footer').css({
      'width': '100%',
      'left': '0'
   });
} else {
   var $_I = $('.places-content.places-all').width();
   var $__I = $('.places-content.places-all').find('.container').width();

   var $half = parseInt($_I) - parseInt($__I);
   $half = parseInt($half) / 2;

   $('.main-footer').css({
      'width': $_I+'px',
      'left': '-'+$half+'px'
   });
}
}
});

$(document).on('click', '.tablist .tab a', function(e) {
$href = $(this).attr('href');
$href = $href.replace('#', '');

$('.places-content').removeClass().addClass('places-content '+$href);


$this = $(this);
});
</script>
<?php $this->endBody() ?> 