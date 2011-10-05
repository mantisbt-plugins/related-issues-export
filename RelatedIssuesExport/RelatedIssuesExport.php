<?php
# Copyright (c) 2011 Robert Munteanu (robert@lmn.ro)

# Inline column configuration for MantisBT is free software: 
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation, 
# either version 2 of the License, or (at your option) any later version.
#
# Inline column configuration plugin for MantisBT is distributed in the hope 
# that it will be useful, but WITHOUT ANY WARRANTY; without even the 
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Inline column configuration plugin for MantisBT.  
# If not, see <http://www.gnu.org/licenses/>.

class RelatedIssuesExportPlugin extends MantisPlugin {
    
    public function register() {
        $this->name = plugin_lang_get("title");
        $this->description = plugin_lang_get("description");

        $this->version = "1.0";
        $this->requires = array(
			"MantisCore" => "1.2.8"
        );

        $this->author = "Robert Munteanu";
        $this->contact = "robert@lmn.ro";
        $this->url ="http://www.mantisbt.org/wiki/doku.php/mantisbt:relatedissuesexport";
    }
    
    public function hooks() {
    
        return array (
            'EVENT_MENU_FILTER' => 'add_related_issues_export_link',
        );
    }
    
    public function add_related_issues_export_link() {
        
        $t_required_level = plugin_config_get('export_access_level_threshold');
        
        if ( ! access_has_project_level( $t_required_level ) )
            return;
        
        // TODO: remove OB once we have echo_link in MantisBT core
        ob_start();
        echo '&#160;';
        print_link( plugin_page('export.php'), plugin_lang_get( 'export_related_issues_link' ));
        
        $link = ob_get_contents();
        
        ob_end_clean();
        
        return $link;
    }
    
    function config() {
        return array(
            'export_access_level_threshold' => DEVELOPER
        );
    }
}
?>