<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.


    // Code based on the excel_xml_export.php from MantisBT
	require_once( 'core.php' );

	require_once( 'current_user_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'string_api.php' );
	require_once( 'columns_api.php' );
	require_once( 'excel_api.php' );

	require( 'print_all_bug_options_inc.php' );

	function RIE_echo_child_cells( $p_child_bug_id , $p_relationship_type ) {

	    $t_bug = bug_get( $p_child_bug_id );
	    $t_latest_bugnote_arr = bugnote_get_all_visible_bugnotes( $p_child_bug_id, 'DESC', 1);
	    $t_latest_bugnote = count ( $t_latest_bugnote_arr ) == 1 ? $t_latest_bugnote_arr[0]->note : "";

	    // set ss:Index = 4 to ensure that the correct column is selected
	    // not required for the first row, but for the rest
	    echo excel_get_cell ( excel_prepare_string ( relationship_get_description_src_side ( $p_relationship_type ) ), 'String', array('ss:Index' => '5') );
	    echo excel_format_id ( $p_child_bug_id );
	    echo excel_format_project_id( $t_bug->project_id );
	    echo excel_get_cell( excel_prepare_string( get_enum_element( 'status', $t_bug->status) ), 'String');
	    echo excel_get_cell( $t_bug->handler_id > 0 ? excel_prepare_string( user_get_name( $t_bug->handler_id  ) ) : '', 'String');
	    echo excel_get_cell( excel_prepare_string( $t_latest_bugnote ), 'String');
	}
	
	auth_ensure_user_authenticated();

	$f_export = gpc_get_string( 'export', '' );

	helper_begin_long_process();
	
	# configure styles
	$t_cell_style = new ExcelStyle('Default_Row');
	$t_cell_style->setBorder('#000000');
	
	$t_header_style = new ExcelStyle('Header_Row');
	$t_header_style->setBorder('#000000');
	$t_header_style->setFont(1);
	
	$t_alt_background_style = new ExcelStyle('Alternate_Row');
	$t_alt_background_style ->setBorder('#000000');
	$t_alt_background_style->setBackgroundColor('#C1C1C1');
	
	$t_styles = array( $t_cell_style, $t_alt_background_style, $t_header_style );

	$t_export_title = excel_get_default_filename();

	$t_short_date_format = config_get( 'short_date_format' );

	# This is where we used to do the entire actual filter ourselves
	$t_page_number = gpc_get_int( 'page_number', 1 );
	$t_per_page = 100;
	$t_bug_count = null;
	$t_page_count = null;

	$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
	if ( $result === false ) {
		print_header_redirect( 'view_all_set.php?type=0&print=1' );
	}

	header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
	header( 'Pragma: public' );
	header( 'Content-Disposition: attachment; filename="' . urlencode( file_clean_name( $t_export_title ) ) . '.xml"' ) ;

	echo excel_get_header( $t_export_title , $t_styles );

	echo excel_get_start_row( $t_header_style->getId() );
	echo excel_get_cell( excel_prepare_string( plugin_lang_get('source_item_id') ), 'String');
	echo excel_get_cell( column_get_title('status') , 'String');
	echo excel_get_cell( column_get_title('handler_id') , 'String');
	echo excel_get_cell( plugin_lang_get('last_note') , 'String');
	echo excel_get_cell( plugin_lang_get('relationship') , 'String');
	echo excel_get_cell( plugin_lang_get('related_items') , 'String');
	echo excel_get_cell( column_get_title('project_id') , 'String');
	echo excel_get_cell( column_get_title('status') , 'String');
	echo excel_get_cell( column_get_title('handler_id') , 'String');
	echo excel_get_cell( plugin_lang_get('last_note') , 'String');
	
	echo excel_get_end_row();

	$f_bug_arr = explode( ',', $f_export );

	do
	{
		$t_more = true;
		$t_row_count = count( $result );
		
		$row_number = 0;

		for( $i = 0; $i < $t_row_count; $i++ ) {
			$t_row = $result[$i];
			$t_bug = null;

			if ( is_blank( $f_export ) || in_array( $t_row->id, $f_bug_arr ) ) {
				$t_flag = false;
				$t_bug_relationships = relationship_get_all_src( $t_row->id );
				
				if ( count ( $t_bug_relationships ) == 0 ) {
				    continue;    
				}
				
				$t_style_id = $t_cell_style->getId();
				$t_merge_attr = array('ss:MergeDown'=> ( count ( $t_bug_relationships ) -1 ));
				$t_latest_bugnote_arr = bugnote_get_all_visible_bugnotes( $t_row->id, 'DESC', 1);
				$t_latest_bugnote = count ( $t_latest_bugnote_arr ) == 1 ? $t_latest_bugnote_arr[0]->note : "";
				$t_first_relationship = array_pop( $t_bug_relationships );
				
				echo excel_get_start_row( $t_style_id );
				echo excel_get_cell( $t_row->id, 'Number', $t_merge_attr );
				echo excel_get_cell( excel_prepare_string( get_enum_element( 'status', $t_row->status) ), 'String', $t_merge_attr );
				echo excel_get_cell( $t_row->handler_id > 0 ? excel_prepare_string( user_get_name( $t_row->handler_id ) ) : '', 'String', $t_merge_attr);
				echo excel_get_cell( excel_prepare_string( $t_latest_bugnote ), 'String', $t_merge_attr);
				
				RIE_echo_child_cells( $t_first_relationship->dest_bug_id, $t_first_relationship->type );

				echo excel_get_end_row();
				$row_number++;
				
				foreach ( $t_bug_relationships as $t_bug_relationship ) {
				    $t_style_id = $row_number % 2 == 1 ? $t_alt_background_style->getId() : $t_cell_style->getId();
				    echo excel_get_start_row( $t_style_id );
				    RIE_echo_child_cells( $t_bug_relationship->dest_bug_id, $t_bug_relationship->type );
				    echo excel_get_end_row();
				    $row_number++;
				}
				

			} #in_array
		} #for loop

		// If got a full page, then attempt for the next one.
		// @@@ Note that since we are not using a transaction, there is a risk that we get a duplicate record or we miss
		// one due to a submit or update that happens in parallel.
		if ( $t_row_count == $t_per_page ) {
			$t_page_number++;
			$t_bug_count = null;
			$t_page_count = null;

			$result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
			if ( $result === false ) {
				$t_more = false;
			}
		} else {
			$t_more = false;
		}
	} while ( $t_more );

	echo excel_get_footer();
