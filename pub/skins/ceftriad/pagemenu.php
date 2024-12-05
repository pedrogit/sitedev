<?php if (!defined('PmWiki')) exit();
	Markup('pagemenu','directives','/\\(:pagemenu\\s*(.*?)\\s?:\\)/', "PageMenu");
	Markup('pagemenuend','directives','/\\(:pagemenuend:\\)/', Keep("</div></div>"));
	Markup('endpagemenu','directives','/\\(:endpagemenu:\\)/', Keep("</div></div>"));

	function PageMenu($m)
	{
		$opt = ParseArgs($m[1]);
		$style = $opt['style'] ?? "";
		$burgerbarstyle = $opt['burgerbarstyle'] ?? "";

		return "<div id='pagemenu' class='pagemenuwrapper' style='$style'>
	  <input type='checkbox'/>
		<div class='pagemenuburger'>
		   <span class='pagemenuburgerbar' style='$burgerbarstyle'></span>
		  <span class='pagemenuburgerbar' style='$burgerbarstyle'></span>
		  <span class='pagemenuburgerbar' style='$burgerbarstyle'></span>
		</div>
		<div class='pagemenucontent'>";
	}
?>