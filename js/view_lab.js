function LabView() {
   window.lv = this;
   window.lv.ecoliAdapter1 = null;
   window.lv.ecoliAdapter2 = null;
   window.lv.ecoliAdapter3 = null;
   window.lv.campyAdapter1 = null;
   window.lv.campyAdapter2 = null;
   $(document).ready(function(){
      window.lv.resetTableVisibility();
      $("#ecoli_table1").show();
      $("#ecoli_table1_hd").show();
      window.lv.initEColiTable1();
      $("#table_to_show").change(function(){
         window.lv.resetTableVisibility();
         $("#"+$("#table_to_show").val()).show();
         $("#"+$("#table_to_show").val()+"_hd").show();
         if($("#ecoli_table1").is(":visible")) {
            window.lv.initEColiTable1();
         }
         if($("#ecoli2_table1").is(":visible")) {
            window.lv.initEColi2Table1();
         }
         if($("#ecoli3_table1").is(":visible")) {
            window.lv.initEColi3Table1();
         }
         if($("#campy_table1").is(":visible")) {
            window.lv.initCampyTable1();
         }
         if($("#campy2_table1").is(":visible")) {
            window.lv.initCampy2Table1();
         }
      });
   });
}

LabView.prototype.resetTableVisibility = function() {
   $("#ecoli_table1").hide();
   $("#ecoli_table1_hd").hide();
   $("#ecoli2_table1").hide();
   $("#ecoli2_table1_hd").hide();
   $("#ecoli3_table1").hide();
   $("#ecoli3_table1_hd").hide();
   $("#campy_table1").hide();
   $("#campy_table1_hd").hide();
   $("#campy2_table1").hide();
   $("#campy2_table1_hd").hide();
};

LabView.prototype.initEColiTable1 = function() {
   var source = {
      datatype: 'json', datafields: [ 
         {name: 'received_samples_id'}, {name: 'received_samples_for_sequencing'}, {name: 'received_samples_sample'}, {name: 'received_samples_user'}, {name : 'received_samples_datetime_received'},
         {name: 'broth_assoc_broth_sample'}, {name: 'broth_assoc_datetime_added'}, {name: 'broth_assoc_field_sample_id'}, {name: 'broth_assoc_user'}, {name: 'broth_assoc_id'},
         {name: 'mcconky_assoc_id'},{name: 'mcconky_assoc_datetime_added'}, {name: 'mcconky_assoc_media_used'}, {name: 'mcconky_assoc_no_qtr_colonies'}, {name: 'mcconky_assoc_plate1_barcode'}
      ],
      id:'received_samples_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table1'}
   };
   // set rows details.
   $("#ecoli_table1").bind('bindingcomplete', function (event) {
      if (event.target.id == "ecoli_table1") {
         $("#ecoli_table1").jqxGrid('beginupdate');
         var datainformation = $("#ecoli_table1").jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            $("#ecoli_table1").jqxGrid('setrowdetails', i, "<div id='table1" + i + "' style='margin: 10px;'></div>", 500, true);
         }
         $("#ecoli_table1").jqxGrid('endupdate');
      }
   });
   window.lv.ecoliAdapter1 = new $.jqx.dataAdapter(source);
   $("#ecoli_table1").jqxGrid({
      width: 917,
      source: source,
      rowdetails: true,
      initrowdetails: window.lv.initEColiTable2,
      pageable: true,
      autoheight: true,
      sortable: true,
      showfilterrow: false,
      autoshowfiltericon: true,
      showstatusbar: true,
      renderstatusbar: window.lv.gridStatusBar,
      filterable: true,
      touchmode: false,
      enabletooltips: false,
      pagesize: 20,
      pagesizeoptions: ['20', '50', '100'],
      columns: [
        { datafield: 'id_received_samples_id', hidden: true },
        { text: 'Date Received', datafield: 'received_samples_datetime_received', width: 130 },
        { text: 'Received Sample BC', datafield: 'received_samples_sample', width: 95},
        { text: 'Received By', datafield: 'received_samples_user', width: 80},
        { text: 'Broth BC', datafield: 'broth_assoc_broth_sample', width: 95 },
        { text: 'Date into Broth', datafield: 'broth_assoc_datetime_added', width: 130 },
        { text: 'Broth User', datafield: 'broth_assoc_user', width: 80 },
        { text: 'Plate 1 BC', datafield: 'mcconky_assoc_plate1_barcode', width: 95 },
        { text: 'Colonies', datafield: 'mcconky_assoc_no_qtr_colonies', width: 80 }
      ]
   });
};

/**
 * Initiate the rendering of the status bar in the animal grid
 * @returns {undefined}
 */
LabView.prototype.gridStatusBar = function(statusbar){
   var container = $("<div style='overflow: hidden; position: relative; margin: 5px;'></div>");
   var excelButton = $("<div class='status_bar_div'><img style='position: relative; margin-top: 2px;' src='images/excel.png'/><span class='status_bar_span'>Export</span></div>");

   container.append(excelButton);
   excelButton.jqxButton({  width: 80, height: 20 });
   statusbar.append(container);

   excelButton.click(function (event) {
       window.location = "mod_ajax.php?page=view&do=get_excel";
   });

   /*$('#showAllId').on('change', function(){
      animals.showAll = $('#showAllId')[0].checked;
      animals.initiateAnimalsGrid();
   });*/
};

LabView.prototype.initEColiTable2 = function(index, parentElement, gridElement, record) {
   console.log("called");
   var id = record.mcconky_assoc_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'colonies_id'}, {name: 'colonies_datetime_saved'}, {name: 'colonies_colony'},{name: 'colonies_user'},
         {name: 'mh_assoc_datetime_added'}, {name: 'mh_assoc_mh'},{name: 'mh_assoc_user'},
         {name: 'mh_vial_id'}, {name: 'mh_vial_datetime_saved'}, {name: 'mh_vial_box'},{name: 'mh_vial_mh_vial'},{name: 'mh_vial_position_in_box'},{name: 'mh_vial_pos_saved_by'},{name: 'mh_vial_user'}
      ],
      id:'colonies_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table2', 'id':id}
   };
   grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2" + i + "' style='margin: 10px;'></div>", 400, true);
         }
         grid.jqxGrid('endupdate');
      }
   });
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColiTable3,
          columns: [
             { text: 'Colony Date', datafield: 'colonies_datetime_saved', width: 130},
             { text: 'Colony BC', datafield: 'colonies_colony', width: 95 },
             { text: 'MH Date', datafield: 'mh_assoc_datetime_added', width: 130 },
             { text: 'MH Barcode', datafield: 'mh_assoc_mh', width: 95 },
             { text: 'MH Vial', datafield: 'mh_vial_mh_vial', width: 95 },
             { text: 'Box', datafield: 'mh_vial_box', width: 80 },
             { text: 'Position', datafield: 'mh_vial_position_in_box', width: 10 }
          ]
      });
   }
};

LabView.prototype.initEColiTable3 = function(index, parentElement, gridElement, record) {
   var id = record.mh_vial_id;
   console.log(id);
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'mh2_assoc_datetime_added'}, {name: 'mh2_assoc_mh'},{name: 'mh2_assoc_user'},{name: 'mh2_assoc_id'},
         {name: 'plate2_id'}, {name: 'plate2_datetime_added'}, {name: 'plate2_plate'},{name: 'plate2_user'}
      ],
      id:'plate2_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table3', 'id':id}
   };
   grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table3" + i + "' style='margin: 10px;'></div>", 300, true);
         }
         grid.jqxGrid('endupdate');
      }
   });
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColiTable4,
          columns: [
             { text: 'Plating Date', datafield: 'plate2_datetime_added', width: 130},
             { text: 'Plate2 Barcode', datafield: 'plate2_plate', width: 95 },
             { text: 'MH2 Barcode', datafield: 'mh2_assoc_mh', width: 130 }
          ]
      });
   }
};

LabView.prototype.initEColiTable4 = function(index, parentElement, gridElement, record) {
   var id = record.mh2_assoc_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'biochemical_test_datetime_added'}, {name: 'biochemical_test_media'},{name: 'biochemical_test_user'},{name: 'biochemical_test_id'}
      ],
      id:'biochemical_test_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table4', 'id':id}
   };
   grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table4" + i + "' style='margin: 10px;'></div>", 200, true);
         }
         grid.jqxGrid('endupdate');
      }
   });
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColiTable5,
          columns: [
             { text: 'B.Chem Date', datafield: 'biochemical_test_datetime_added', width: 130},
             { text: 'B.Chem Media Barcode', datafield: 'biochemical_test_media', width: 170}
          ]
      });
   }
};

LabView.prototype.initEColiTable5 = function(index, parentElement, gridElement, record) {
   var id = record.biochemical_test_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'biochemical_test_results_id'}, {name: 'biochemical_test_results_datetime_added'}, {name: 'biochemical_test_results_observ_type'},{name: 'biochemical_test_results_observ_value'},{name: 'biochemical_test_results_test'},{name: 'biochemical_test_results_user'},{name: 'biochemical_test_results_date_added'}
      ],
      id:'biochemical_test_results_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table5', 'id':id}
   };
   /*grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table5" + i + "' style='margin: 10px;'></div>", 100, true);
         }
         grid.jqxGrid('endupdate');
      }
   });*/
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          //rowdetails: true,
          //initrowdetails: window.lv.initEColiTable3,
          columns: [
             { text: 'Date of Result', datafield: 'biochemical_test_results_datetime_added', width: 130},
             { text: 'B.Chem Test', datafield: 'biochemical_test_results_test', width: 95 },
             { text: 'B.Chem Type', datafield: 'biochemical_test_results_observ_type', width: 120 },
             { text: 'B.Chem Value', datafield: 'biochemical_test_results_observ_value', width: 120 }
          ]
      });
   }
};

LabView.prototype.initEColi2Table1 = function() {
   var source = {
      datatype: 'json', datafields: [ 
         {name: 'received_samples_id'}, {name: 'received_samples_for_sequencing'}, {name: 'received_samples_sample'}, {name: 'received_samples_user'}, {name : 'received_samples_datetime_received'},
         {name: 'broth_assoc_broth_sample'}, {name: 'broth_assoc_datetime_added'}, {name: 'broth_assoc_field_sample_id'}, {name: 'broth_assoc_user'}, {name: 'broth_assoc_id'},
         {name: 'mcconky_assoc_id'},{name: 'mcconky_assoc_datetime_added'}, {name: 'mcconky_assoc_media_used'}, {name: 'mcconky_assoc_no_qtr_colonies'}, {name: 'mcconky_assoc_plate1_barcode'}
      ],
      id:'received_samples_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table1'}
   };
   // set rows details.
   $("#ecoli2_table1").bind('bindingcomplete', function (event) {
      if (event.target.id == "ecoli2_table1") {
         $("#ecoli2_table1").jqxGrid('beginupdate');
         var datainformation = $("#ecoli2_table1").jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            $("#ecoli2_table1").jqxGrid('setrowdetails', i, "<div id='table1" + i + "' style='margin: 10px;'></div>", 500, true);
         }
         $("#ecoli2_table1").jqxGrid('endupdate');
      }
   });
   window.lv.ecoliAdapter2 = new $.jqx.dataAdapter(source);
   $("#ecoli2_table1").jqxGrid({
      width: 917,
      source: source,
      rowdetails: true,
      initrowdetails: window.lv.initEColi2Table2,
      columns: [
        { datafield: 'id_received_samples_id', hidden: true },
        { text: 'Date Received', datafield: 'received_samples_datetime_received', width: 130 },
        { text: 'Received Sample BC', datafield: 'received_samples_sample', width: 95},
        { text: 'Received By', datafield: 'received_samples_user', width: 80},
        { text: 'Broth BC', datafield: 'broth_assoc_broth_sample', width: 95 },
        { text: 'Date into Broth', datafield: 'broth_assoc_datetime_added', width: 130 },
        { text: 'Broth User', datafield: 'broth_assoc_user', width: 80 },
        { text: 'Plate 1 BC', datafield: 'mcconky_assoc_plate1_barcode', width: 95 },
        { text: 'Colonies', datafield: 'mcconky_assoc_no_qtr_colonies', width: 80 }
      ]
   });
};

LabView.prototype.initEColi2Table2 = function(index, parentElement, gridElement, record) {
   var id = record.mcconky_assoc_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'mh_assoc_datetime_added'}, {name: 'mh_assoc_mh'},{name: 'mh_assoc_user'},
         {name: 'colonies_id'}, {name: 'colonies_datetime_saved'}, {name: 'colonies_colony'},{name: 'colonies_user'},
         {name: 'mh_vial_id'}, {name: 'mh_vial_datetime_saved'}, {name: 'mh_vial_box'},{name: 'mh_vial_mh_vial'},{name: 'mh_vial_position_in_box'},{name: 'mh_vial_pos_saved_by'},{name: 'mh_vial_user'}
      ],
      id:'colonies_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table2', 'id':id}
   };
   grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2-2" + i + "' style='margin: 10px;'></div>", 400, true);
         }
         grid.jqxGrid('endupdate');
      }
   });
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColi2Table3,
          columns: [
             { text: 'Colony Date', datafield: 'colonies_datetime_saved', width: 130},
             { text: 'Colony BC', datafield: 'colonies_colony', width: 95 },
             { text: 'MH Date', datafield: 'mh_assoc_datetime_added', width: 130 },
             { text: 'MH Barcode', datafield: 'mh_assoc_mh', width: 95 },
             { text: 'MH Vial', datafield: 'mh_vial_mh_vial', width: 95 },
             { text: 'Box', datafield: 'mh_vial_box', width: 80 },
             { text: 'Position', datafield: 'mh_vial_position_in_box', width: 10 }
          ]
      });
   }
};

LabView.prototype.initEColi2Table3 = function(index, parentElement, gridElement, record) {
   var id = record.mh_vial_id;
   console.log(id);
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'mh3_assoc_datetime_added'}, {name: 'mh3_assoc_mh'},{name: 'mh3_assoc_user'},{name: 'mh3_assoc_id'},
         {name: 'plate3_id'}, {name: 'plate3_datetime_added'}, {name: 'plate3_plate'},{name: 'plate3_user'}
      ],
      id:'plate3_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table2-3', 'id':id}
   };
   grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2-3" + i + "' style='margin: 10px;'></div>", 300, true);
         }
         grid.jqxGrid('endupdate');
      }
   });
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColi2Table4,
          columns: [
             { text: 'Plating Date', datafield: 'plate3_datetime_added', width: 130},
             { text: 'Plate3 Barcode', datafield: 'plate3_plate', width: 95 },
             { text: 'MH3 Barcode', datafield: 'mh3_assoc_mh', width: 130 }
          ]
      });
   }
};

LabView.prototype.initEColi2Table4 = function(index, parentElement, gridElement, record) {
   var id = record.mh3_assoc_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'plate45_id'}, {name: 'plate45_datetime_added'},{name: 'plate45_number'},{name: 'plate45_plate'},{name: 'plate45_user'}
      ],
      id:'plate45_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table2-4', 'id':id}
   };
   grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2-4" + i + "' style='margin: 10px;'></div>", 400, true);
         }
         grid.jqxGrid('endupdate');
      }
   });
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColi2Table5,
          columns: [
             { text: 'Plate4/5 Date', datafield: 'plate45_datetime_added', width: 130},
             { text: 'Number', datafield: 'plate45_number', width: 130 },
             { text: 'Plate', datafield: 'plate45_plate', width: 130 },
             { text: 'User Plating', datafield: 'plate45_user', width: 130 }
          ]
      });
   }
};

LabView.prototype.initEColi2Table5 = function(index, parentElement, gridElement, record) {
   console.log("blah");
   var id = record.plate45_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'ast_result_id'}, {name: 'ast_result_datetime_added'}, {name: 'ast_result_drug'},{name: 'ast_result_user'},{name: 'ast_result_value'}
      ],
      id:'ast_result_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table2-5', 'id':id}
   };
   /*grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2-5" + i + "' style='margin: 10px;'></div>", 400, true);
         }
         grid.jqxGrid('endupdate');
      }
   });*/
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          //rowdetails: true,
          //initrowdetails: window.lv.initEColiTable3,
          columns: [
             { text: 'Date of AST Test', datafield: 'ast_result_datetime_added', width: 130},
             { text: 'Drug', datafield: 'ast_result_drug', width: 95 },
             { text: 'Value', datafield: 'ast_result_value', width: 95 },
             { text: 'Recorded by', datafield: 'ast_result_user', width: 95 }
          ]
      });
   }
};

LabView.prototype.initEColi3Table1 = function() {
   var source = {
      datatype: 'json', datafields: [ 
         {name: 'received_samples_id'}, {name: 'received_samples_for_sequencing'}, {name: 'received_samples_sample'}, {name: 'received_samples_user'}, {name : 'received_samples_datetime_received'},
         {name: 'broth_assoc_broth_sample'}, {name: 'broth_assoc_datetime_added'}, {name: 'broth_assoc_field_sample_id'}, {name: 'broth_assoc_user'}, {name: 'broth_assoc_id'},
         {name: 'mcconky_assoc_id'},{name: 'mcconky_assoc_datetime_added'}, {name: 'mcconky_assoc_media_used'}, {name: 'mcconky_assoc_no_qtr_colonies'}, {name: 'mcconky_assoc_plate1_barcode'}
      ],
      id:'received_samples_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table1'}
   };
   // set rows details.
   $("#ecoli3_table1").bind('bindingcomplete', function (event) {
      if (event.target.id == "ecoli3_table1") {
         $("#ecoli3_table1").jqxGrid('beginupdate');
         var datainformation = $("#ecoli3_table1").jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            $("#ecoli3_table1").jqxGrid('setrowdetails', i, "<div id='table1" + i + "' style='margin: 10px;'></div>", 500, true);
         }
         $("#ecoli3_table1").jqxGrid('endupdate');
      }
   });
   window.lv.ecoliAdapter3 = new $.jqx.dataAdapter(source);
   $("#ecoli3_table1").jqxGrid({
      width: 917,
      source: source,
      rowdetails: true,
      initrowdetails: window.lv.initEColi3Table2,
      columns: [
        { datafield: 'id_received_samples_id', hidden: true },
        { text: 'Date Received', datafield: 'received_samples_datetime_received', width: 130 },
        { text: 'Received Sample BC', datafield: 'received_samples_sample', width: 95},
        { text: 'Received By', datafield: 'received_samples_user', width: 80},
        { text: 'Broth BC', datafield: 'broth_assoc_broth_sample', width: 95 },
        { text: 'Date into Broth', datafield: 'broth_assoc_datetime_added', width: 130 },
        { text: 'Broth User', datafield: 'broth_assoc_user', width: 80 },
        { text: 'Plate 1 BC', datafield: 'mcconky_assoc_plate1_barcode', width: 95 },
        { text: 'Colonies', datafield: 'mcconky_assoc_no_qtr_colonies', width: 80 }
      ]
   });
};

LabView.prototype.initEColi3Table2 = function(index, parentElement, gridElement, record) {
   var id = record.mcconky_assoc_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'colonies_id'}, {name: 'colonies_datetime_saved'}, {name: 'colonies_colony'},{name: 'colonies_user'},
         {name: 'mh_assoc_datetime_added'}, {name: 'mh_assoc_mh'},{name: 'mh_assoc_user'},
         {name: 'mh_vial_id'}, {name: 'mh_vial_datetime_saved'}, {name: 'mh_vial_box'},{name: 'mh_vial_mh_vial'},{name: 'mh_vial_position_in_box'},{name: 'mh_vial_pos_saved_by'},{name: 'mh_vial_user'}
      ],
      id:'colonies_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table2', 'id':id}
   };
   grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2-2" + i + "' style='margin: 10px;'></div>", 200, true);
         }
         grid.jqxGrid('endupdate');
      }
   });
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColi3Table3,
          columns: [
             { text: 'Colony Date', datafield: 'colonies_datetime_saved', width: 130},
             { text: 'Colony BC', datafield: 'colonies_colony', width: 95 },
             { text: 'MH Date', datafield: 'mh_assoc_datetime_added', width: 130 },
             { text: 'MH Barcode', datafield: 'mh_assoc_mh', width: 95 },
             { text: 'MH Vial', datafield: 'mh_vial_mh_vial', width: 95 },
             { text: 'Box', datafield: 'mh_vial_box', width: 80 },
             { text: 'Position', datafield: 'mh_vial_position_in_box', width: 10 }
          ]
      });
   }
};

LabView.prototype.initEColi3Table3 = function(index, parentElement, gridElement, record) {
   var id = record.mh_vial_id;
   console.log(id);
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'plate6_id'}, {name: 'plate6_datetime_added'}, {name: 'plate6_plate'},{name: 'plate6_user'},
         {name: 'mh6_assoc_datetime_added'}, {name: 'mh6_assoc_mh'},{name: 'mh6_assoc_user'},{name: 'mh6_assoc_id'},
         {name: 'dna_eppendorfs_eppendorf'},{name : 'dna_eppendorfs_user'}
      ],
      id:'plate6_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'table3-3', 'id':id}
   };
   /*grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2-3" + i + "' style='margin: 10px;'></div>", 300, true);
         }
         grid.jqxGrid('endupdate');
      }
   });*/
   if (grid != null) {
       grid.jqxGrid({
          width: 685,
          source: source,
          rowdetails: true,
          //initrowdetails: window.lv.initEColi3Table4,
          columns: [
             { text: 'Plating Date', datafield: 'plate6_datetime_added', width: 130},
             { text: 'Plate6 Barcode', datafield: 'plate6_plate', width: 95 },
             { text: 'MH6 Barcode', datafield: 'mh6_assoc_mh', width: 130 },
             { text: 'DNA Eppendorf', datafield: 'dna_eppendorfs_eppendorf', width: 130 },
             { text: 'User Responsible', datafield: 'dna_eppendorfs_user', width: 130 }
          ]
      });
   }
};

LabView.prototype.initCampyTable1 = function() {
   var source = {
      datatype: 'json', datafields: [ 
         {name: 'campy_received_bootsocks_id'}, {name: 'campy_received_bootsocks_datetime_received'}, {name: 'campy_received_bootsocks_for_sequencing'}, {name: 'campy_received_bootsocks_sample'}, {name: 'campy_received_bootsocks_user'},
         {name: 'campy_bootsock_assoc_id'}, {name: 'campy_bootsock_assoc_daughter_sample'}, {name: 'campy_bootsock_assoc_datetime_added'}, {name: 'campy_bootsock_assoc_user'},
         {name: 'campy_cryovials_cryovial'}, {name: 'campy_cryovials_datetime_saved'}, {name: 'campy_cryovials_id'}, {name: 'campy_cryovials_position_in_box'}, {name: 'campy_cryovials_user'}, {name: 'campy_cryovials_box'}
      ],
      id:'campy_received_bootsocks_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'campy1'}
   };
   // set rows details.
   /*$("#campy_table1").bind('bindingcomplete', function (event) {
      if (event.target.id == "campy_table1") {
         $("#campy_table1").jqxGrid('beginupdate');
         var datainformation = $("#campy_table1").jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            $("#campy_table1").jqxGrid('setrowdetails', i, "<div id='table1" + i + "' style='margin: 10px;'></div>", 500, true);
         }
         $("#campy_table1").jqxGrid('endupdate');
      }
   });*/
   window.lv.campyAdapter1 = new $.jqx.dataAdapter(source);
   $("#campy_table1").jqxGrid({
      width: 917,
      source: source,
      rowdetails: true,
      //initrowdetails: window.lv.initCampyTable2,
      columns: [
        { datafield: 'id_received_samples_id', hidden: true },
        { text: 'Date Received', datafield: 'campy_received_bootsocks_datetime_received', width: 130 },
        { text: 'Received Bootsock/Pot', datafield: 'campy_received_bootsocks_sample', width: 95},
        { text: 'Received By', datafield: 'campy_received_bootsocks_user', width: 80},
        { text: 'Falcon Tube', datafield: 'campy_bootsock_assoc_daughter_sample', width: 95 },
        { text: 'Cryovial', datafield: 'campy_cryovials_cryovial', width: 130 },
        { text: 'Storage box', datafield: 'campy_cryovials_box', width: 80 },
        { text: 'Position in Box', datafield: 'campy_cryovials_position_in_box', width: 95 }
      ]
   });
};

LabView.prototype.initCampy2Table1 = function() {
   var source = {
      datatype: 'json', datafields: [ 
         {name: 'campy_received_bootsocks_id'}, {name: 'campy_received_bootsocks_datetime_received'}, {name: 'campy_received_bootsocks_for_sequencing'}, {name: 'campy_received_bootsocks_sample'}, {name: 'campy_received_bootsocks_user'},
         {name: 'campy_bootsock_assoc_id'}, {name: 'campy_bootsock_assoc_daughter_sample'}, {name: 'campy_bootsock_assoc_datetime_added'}, {name: 'campy_bootsock_assoc_user'},
         {name: 'campy_mccda_assoc_datetime_added'},{name: 'campy_mccda_assoc_plate1_barcode'}, {name: 'campy_mccda_assoc_user'}, {name: 'campy_mccda_assoc_id'}
      ],
      id:'campy_received_bootsocks_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'campy2-1'}
   };
   // set rows details.
   $("#campy2_table1").bind('bindingcomplete', function (event) {
      if (event.target.id == "campy2_table1") {
         $("#campy2_table1").jqxGrid('beginupdate');
         var datainformation = $("#campy2_table1").jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            $("#campy2_table1").jqxGrid('setrowdetails', i, "<div id='table1" + i + "' style='margin: 10px;'></div>", 300, true);
         }
         $("#campy2_table1").jqxGrid('endupdate');
      }
   });
   window.lv.campyAdapter2 = new $.jqx.dataAdapter(source);
   $("#campy2_table1").jqxGrid({
      width: 917,
      source: source,
      rowdetails: true,
      initrowdetails: window.lv.initCampy2Table2,
      columns: [
        { datafield: 'id_received_samples_id', hidden: true },
        { text: 'Date Received', datafield: 'campy_received_bootsocks_datetime_received', width: 130 },
        { text: 'Received Bootsock/Pot', datafield: 'campy_received_bootsocks_sample', width: 95},
        { text: 'Received By', datafield: 'campy_received_bootsocks_user', width: 80},
        { text: 'Falcon Tube', datafield: 'campy_bootsock_assoc_daughter_sample', width: 95 },
        { text: 'MCCDA Plate', datafield: 'campy_mccda_assoc_plate1_barcode', width: 130 },
        { text: 'Plating Date', datafield: 'campy_mccda_assoc_datetime_added', width: 80 },
        { text: 'Plating User', datafield: 'campy_mccda_assoc_user', width: 95 }
      ]
   });
};

LabView.prototype.initCampy2Table2 = function(index, parentElement, gridElement, record) {
   var id = record.campy_mccda_assoc_id;
   var grid = $($(parentElement).children()[0]);
   var gridId = grid.attr('id');
   var source = {
      datatype: 'json', datafields: [
         {name: 'campy_mccda_growth_am_plate'}, {name: 'campy_mccda_growth_datetime_saved'}, {name: 'campy_mccda_growth_user'},
         {name: 'campy_colonies_colony'}, {name: 'campy_colonies_box'}, {name: 'campy_colonies_position_in_box'}, {name: 'campy_colonies_user'}
      ],
      id:'colonies_id', async: true, type: 'POST', url: 'mod_ajax.php?page=view&do=get_data', data: {'type':'campy2-2', 'id':id}
   };
   /*grid.bind('bindingcomplete', function (event) {
      if (event.target.id == gridId) {
         grid.jqxGrid('beginupdate');
         var datainformation = grid.jqxGrid('getdatainformation');
         for (i = 0; i < datainformation.rowscount; i++) {
            grid.jqxGrid('setrowdetails', i, "<div id='table2-2" + i + "' style='margin: 10px;'></div>", 400, true);
         }
         grid.jqxGrid('endupdate');
      }
   });*/
   if (grid != null) {
       grid.jqxGrid({
          width: 490,
          source: source,
          rowdetails: true,
          initrowdetails: window.lv.initEColi2Table3,
          columns: [
             { text: 'Colony Date', datafield: 'campy_mccda_growth_datetime_saved', width: 130 },
             { text: 'MCCDA Colony', datafield: 'campy_mccda_growth_am_plate', width: 130},
             { text: 'Storage Box', datafield: 'campy_colonies_box', width: 95 },
             { text: 'Position in Box', datafield: 'campy_colonies_position_in_box', width: 130 }
          ]
      });
   }
};