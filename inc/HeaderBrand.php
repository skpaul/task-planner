<?php
declare(strict_types=1);
class HeaderBrand{
    private static function includeHam(bool $condition){
        $html = "";
        if($condition){
            $html= <<<HTML
                  <div class="ham-menu-container">
                        <div  class="hamb" id="hambItem" style="display: block;">☰</div>
                        <div class="hamb" id="hambClose" style="display: none;">✕</div>
                    </div>
            HTML;
        }

        return $html;
    }

    /**
     * Create()
     * 
     * This method has dynamic argument(s).
     * 
     * Arguments- baseUrl- url, hambMenu-true/false, container- container/container-fluid
     */
    public static function prepare(array $params):string
    {
        $baseUrl =  $params["baseUrl"]; 
        $hamIcon = self::includeHam($params["hambMenu"]);
        $container = isset($params["container"])? $params["container"] : "container";
        $orgName = ORGANIZATION_FULL_NAME;
        $html = <<<HTML
            <div class="brand-container">
                <div class="$container">
                    <div class="brand">
                        <img class="logo" src="$baseUrl/assets/images/winbip-logo.png" alt="Winbip Logo">
                        <div style="flex:1; margin-left: 0.4rem;">
                            <div class="govt-name" >&nbsp;Winbip Solutions</div>
                            <div class="org">$orgName</div>
                        </div>
                        $hamIcon
                    </div>
                </div>
            </div>
                        
        HTML;

        return $html;
    }
}
?>


