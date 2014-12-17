
/*global jQuery */
/*global $ */
/*global jS */
$.extend({
    URLEncode:function(c){
        var o='';
        var x=0;
        c=c.toString();
        var r=/(^[a-zA-Z0-9_.]*)/;
        while(x<c.length){
            var m=r.exec(c.substr(x));
            if(m!==null && m.length>1 && m[1]!==''){
                o+=m[1];
                x+=m[1].length;
            }else{
                if(c[x]==' ')o+='+';
                else{
                    var d=c.charCodeAt(x);
                    var h=d.toString(16);
                    o+='%'+(h.length<2?'0':'')+h.toUpperCase();
                }
                x++;
            }
        }
        return o;
    },
    URLDecode:function(s){
        var o=s;
        var binVal,t;
        var r=/(%[^%]{2})/;
        while((m=r.exec(o))!==null && m.length>1 && m[1]!==''){
            b=parseInt(m[1].substr(1),16);
            t=String.fromCharCode(b);
            o=o.replace(m[1],t);
        }
        return o;
    }
});
var lssheet = {};
var lsspreadsheetchart = false;

function htmlEncode(value){
    return $('<div/>').text(value).html();
}

function htmlDecode(value){
    return $('<div/>').html(value).text();
}

function isEncHTML(str) {
    if(str.search(/&amp;/g) != -1 || str.search(/&lt;/g) != -1 || str.search(/&gt;/g) != -1)
        return true;
    else
        return false;
}

function decHTMLifEnc(str){
    if(typeof(str)==="string"){
        if(isEncHTML(str))
            return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g,' ');
        return str;
    }
}

function cleanHTML(str)
{
    if(typeof(str)==="string") {
        return str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g,' ');
    } else {
        return str;
    }
}

function toggleView(){


    var view = jQuery('#toggleView').attr('view');
    saveSStoPage();
    if(view === "formula"){
        calcPHP();
        jQuery('#toggleView span').html("Switch to  Formula View");
        jQuery('#viewStatus').html("Calculated View");
        view = "calculated";
        jQuery('#toggleView').attr('view',view);
    }
    else {
        load_default_lsspreaddata_new();
        jQuery('#toggleView span').html("Switch to Calc View");
        jQuery('#viewStatus').html("Formula view");
        view = "formula";
        jQuery('#toggleView').attr('view',view);
    }
}

function saveSStoPage (){
    jS.evt.cellEditDone();
    var tableJson = jS.exportSheet.json();

    lssheet.sheetdata = tableJson;
    
    if(lsspreadsheetchart === true){
        update_lssheet_chartdata();
        tableJson[0].chartdata = lssheet.chartdata;
    }
    else
    {
        delete lssheet.chartdata;
        tableJson[0].chartdata = null;
    }
        
    var textdata = $.toJSON(tableJson);
    $('textarea[name=lsspreaddata]').val(textdata);
    return tableJson;
}


function calcPHP()
{

    saveSStoPage();
    var sdata = jS.exportSheet.json();
    sdata = sdata[0];
    //sdata = $.toJSON(sdata);
    var dataString = $.toJSON(sdata);


    $.post('ajax_calc_spreadsheet.php', {
        data:sdata
    }, function (jsonCalc) {
        var cell;
        //var spreadSheet = $.evalJSON(jsonCalc);
        var spreadSheet =jsonCalc;
        for (var cellref in spreadSheet){
            $('#viewStatus').html('<img src="ajax-loader.gif"></img>');
            cell = spreadSheet[cellref];
            $('#' + cellref).text(cleanHTML(cell.textvalue));
        }
        $('#viewStatus').html("Calculated View");
    }, "json");

}

function get_lssheet(){
    
    lssheet.data = htmlDecode($('textarea[name=lsspreaddata]').val());
    
    if(lssheet.data !== ""){
        lssheet = eval(lssheet.data);
        lssheet = lssheet[0];
    }
    
}

function load_default_lsspreaddata_new()
{
    var htmlform = {};
    htmlform.data = htmlDecode($('textarea[name=lsspreaddata]').val());
    if(htmlform.data !== ""){
        var celldata = eval(htmlform.data);
        celldata = celldata[0];
        celldata = celldata.cell;
        celldata = celldata;
        var properties ="",type="";
        for (var cellid in celldata)
        {
            if (!(celldata[cellid] instanceof Function))
            {
                var jqcell = $('#' + cellid);
                jqcell[0].className = jqcell[0].className.replace(/\bls.*?\b/g, '');
                for (var property in celldata[cellid])
                {
                    properties += property + ", ";
                    if(property==="feedback"){
                        celldata[cellid].feedback =  $.URLDecode(celldata[cellid].feedback);
                    }
                    jqcell.attr(property, celldata[cellid][property]);
                    if(celldata[cellid].formula !== "")
                    {
                        jqcell.text(celldata[cellid].formula);
                    }
                    else{
                        var celltext = cleanHTML(celldata[cellid].textvalue);

                        jqcell.text(celltext);
                    }
                    if ((property === "celltype") || (property === "rangetype"))
                    {
                        type = celldata[cellid][property].split("_");
                        jqcell.addClass("ls" + type[0]);
                    }
                    if (property === "chart")
                    {
                        type = celldata[cellid][property];
                        jqcell.addClass("ls_" + type+"_cell");
                    }
                }
            }
        }
    }
}

$(document).ready(function () {

    get_lssheet();

    $('.flsspreadsheet').parent().attr('id', 'formelement_lsspreadsheet');
    $('.flsspreadsheet').attr('id', 'flsspreadsheet');
    $('#formelement_lsspreadsheet').children('.fitemtitle').remove();
    
    $('#lscontrols_question_status_text').text('question loaded\n');

    $('#jQuerySheet').sheet({
        title: 'LS SpreadSheet Question',
        inlineMenu: jQuery('#inlineMenu').html(),
        buildSheet: '20x100',
        calcOff: true
    });


    $('#lscontrols_chart_button').button();



    $('#lscontrols_xseriesbutton').button();
    $('#lscontrols_yseriesbutton').button();
    $('#lscontrols_previewchart').button();

    $('#lscontrols_xseriesbutton').click(function(){
        var modcell;
        if(jS.obj.lsSelected.length === 0){
            modcell = $(jS.cellLast.td);
            jS.obj.lsSelected[modcell[0].id] = modcell[0].id;
        }
        for (var cell in jS.obj.lsSelected){
            modcell = $('#' + cell);
            modcell.removeClass('ls_xseries_cell');
            modcell.removeClass('ls_yseries_cell');
            modcell.removeClass('ls');            
            modcell.addClass('ls_xseries_cell');
            modcell.attr('chart', 'xseries');
            
        }
    });

    $('#lscontrols_yseriesbutton').click(function(){
        var modcell;
        if(jS.obj.lsSelected.length === 0){
            modcell = $(jS.cellLast.td);
            jS.obj.lsSelected[modcell[0].id]=modcell[0].id;
        }
        for (var cell in jS.obj.lsSelected){
            modcell = $('#' + cell);
            modcell.removeClass('ls_xseries_cell');
            modcell.removeClass('ls_yseries_cell');
            modcell.removeClass('ls');     
            modcell.addClass('ls_yseries_cell');
            modcell.attr('chart', 'yseries');
        }
    });


    
    if($('input[name="gradingtype"]').attr("value") === ""){
        $('input[name="gradingtype"]').attr("value","auto");
        $(':radio[value="auto"]').attr('checked', true);
    }
    else{
        $(':radio[value="manual"]').attr('checked', true);
    }
    
    var question_gradingtype = $('input[name="gradingtype"]').attr("value");
    
    $('input[name=gradingtype_rb]:radio').filter("[value="+question_gradingtype+"]").attr("checked","checked");
   
    
    if(lssheet.chartdata !== null){
        $('#lscontrols_chart_options').show();
        update_chartdata_from_lssheet();
        lsspreadsheetchart = true;
    }
    else
    {
        $('#lscontrols_chart_options').hide();
        lsspreadsheetchart = false;
    }

    $("input[name='gradingtype_rb']").change(function(){
        if ($("input[name='gradingtype_rb']:checked").val() == 'manual')
        {
            $("input[name='gradingtype']").val("manual");
        }
        else if ($("input[name='gradingtype_rb']:checked").val() == 'auto')
        {
            $("input[name='gradingtype']").val("auto");
        }

    });

    $('#lscontrols_chart_button').click(function(){
 
        if(lsspreadsheetchart === false){
            $('#lscontrols_chart_options').slideDown();
            lsspreadsheetchart = true;
            update_lssheet_chartdata();
            $(':radio[value="manual"]').attr('checked', true);
        }
        else{
            lsspreadsheetchart = false;
            delete lssheet.chartdata;
            $('#lscontrols_chart_options').slideUp();
            $(':radio[value="auto"]').attr('checked', true);
        }
        
        saveSStoPage();
    });

    $('#id_showFormulae').click(function () {
        load_default_lsspreaddata_new();
    });

    $('#load-json-button').click(function(){
        // var jsonInput = $('#json-input').val();
        // $('textarea[name=lsspreaddata]').val(jsonInput);
        load_default_lsspreaddata_new();
    });

    //LOAD the saved data (if any) written from set_data in edit_lsspreadsheet_form.php


    $('#id_submitbutton').click(function () {
        saveSStoPage();
        $('#lsspreaddataForm').submit();
    });

    $('#showJson').click(function () {
        $('#lsspread-test').html(JSON.stringify(saveSStoPage(), undefined, 2));
    });

    load_default_lsspreaddata_new();

    $('#lsscontrols_div :radio').change(function () {
        var option = this.value.split('_')[0];
        var type = this.value.split('_')[1];
        var attribute = this.value.split('_')[2];
        var modcell;
        if(type==="CalcAnswer"){
            attribute = $('#cell_marks').val();
        }

        var rangeval = $('#cell_rangeval').val();
        if(jS.obj.lsSelected.length === 0){
            modcell = $(jS.cellLast.td);
            jS.obj.lsSelected[modcell[0].id]=modcell[0].id;
        }
        for (var cell in jS.obj.lsSelected){
            modcell = $('#' + cell);
            if (option === "celltype") {
                // jqcell[0].className.replace(/\bls.*?\b/g, '');
                modcell.removeClass('lsFixedAnswer');
                modcell.removeClass('lsCalcAnswer');
                modcell.removeClass('lsNumberAnswer');
                modcell.removeClass('lsStudentInput');
                modcell.removeClass('lsLabel');
                modcell.removeClass('lsNone');
                modcell.addClass('ls' + type);
                modcell.attr(option, type + "_" + attribute);
                if(type=="CalcAnswer"){
                    $('#lsscontrols_div :text[name=cell_mark]').val(attribute);
                    var range = $('#lsscontrols_div :radio[name=range_type]:checked"').val();
                    range = range.split('_')[1];
                    modcell.attr('rangetype', range + "_" + rangeval);
                }
                 if(type=="None"){
                   modcell.removeClass('ls_xseries_cell');
                   modcell.removeClass('ls_yseries_cell');
                   modcell.removeClass('ls');     
                   modcell.attr("chart","");
                 }
            }
            else if (option === "rangetype") {
                modcell.removeClass('lsNoRange');
                modcell.removeClass('lsAbsoluteRange');
                modcell.removeClass('lsPercentRange');
                modcell.removeClass('lsSigfigRange');
                modcell.removeClass('lsDecimalRange');
                modcell.addClass('ls' + type);
                modcell.attr(option, type + "_" + rangeval);
                $('#lsscontrols_div :text[name=range_value]').val(rangeval);
            }
            else {
                modcell.attr(option, type + "_" + attribute);
            }
        }
    });


    $('#lsscontrols_div :text').keypress(function (event) {
        if (event.keyCode == '13') {
            event.preventDefault();
        }
    });

    $('#lsscontrols_div :text[name=cell_mark]').keyup(function () {
        var modcell;
        if(jS.obj.lsSelected.length === 0){
            modcell = $(jS.cellLast.td);
            jS.obj.lsSelected[modcell[0].id]=modcell[0].id;
        }
        for (var cell in jS.obj.lsSelected){
            modcell = $('#' + cell);

            var celltype = modcell.attr('celltype');
            celltype = celltype.split('_')[0];
            var marks = this.value;
            if (marks === undefined) {
                marks = "1";
            }

            modcell.attr('celltype',celltype+"_"+this.value);
        }
    });



    $('#lsscontrols_div :text[name=range_value]').keyup(function () {
        var modcell;
        if(jS.obj.lsSelected.length === 0){
            modcell = $(jS.cellLast.td);
            jS.obj.lsSelected[modcell[0].id]=modcell[0].id;
        }
        for (var cell in jS.obj.lsSelected){
            modcell = $('#' + cell);
            var rangetype = modcell.attr('rangetype');
            rangetype = rangetype.split('_')[0];
            var rangeval = this.value;
            if (rangeval === undefined) {
                rangeval = "0";
            }

            modcell.attr('rangetype',rangetype+"_"+this.value);
        }
    });
    $('#cell_feedback').keyup(function () {
        var modcell;
        if(jS.obj.lsSelected.length === 0){
            modcell = $(jS.cellLast.td);
            jS.obj.lsSelected[modcell[0].id]=modcell[0].id;
        }
        for (var cell in jS.obj.lsSelected){
            modcell = $('#' + cell);
            var feedback = htmlEncode(this.value);
            modcell.attr('feedback',feedback);
        }
    });


});

function update_chartdata_from_lssheet(){
    get_lssheet();
    if((lssheet.chartdata !== null) && (lssheet.chartdata !== undefined)){
    $('#lscontrols_chart_title').val(decHTMLifEnc(lssheet.chartdata.series[0].title));
    $('#lscontrols_chart_mark').val(lssheet.chartdata.series[0].mark);
    $('#lscontrols_xseries_title').val(decHTMLifEnc(lssheet.chartdata.series[0].xaxistitle));
    $('#lscontrols_yseries_title').val(decHTMLifEnc(lssheet.chartdata.series[0].yaxistitle));
    }
}

function update_lssheet_chartdata(){
    var chartdata = {};
    chartdata.series = [];
    chartdata.series.push({});
    chartdata.series[0].title = htmlEncode($('#lscontrols_chart_title').val());
    chartdata.series[0].mark = $('#lscontrols_chart_mark').val();
    chartdata.series[0].xaxistitle = htmlEncode($('#lscontrols_xseries_title').val());
    chartdata.series[0].yaxistitle = htmlEncode($('#lscontrols_yseries_title').val());
    lssheet.chartdata = chartdata;
}

function update_question_status(message){
    document.console.log(message);
}


function lsCellSetActive(td, loc)
{
    var modcell = $(jS.cellLast.td);
    var type = td.attr("celltype");
    var range = td.attr("rangetype");
    var feedback = decHTMLifEnc(td.attr("feedback"));
    //addtooltippy?


    if (type !== undefined) {
        celltype = type.split('_')[0];
        if(celltype==="CalcAnswer"){
            setOptionsCalcAnswer();
            var marks = type.split('_')[1];
            $('#cell_marks').val(marks);
        }
        else if(celltype==="Label")
        {
            celltype = type;
        }
        else{
            setOptionsOther();
        }

        $(':radio[value="celltype_'+celltype+'"]').attr('checked', true);

    }
    else {
        setOptionsOther();
        $(':radio[value="celltype_'+'None'+'"]').attr('checked', true);
        $('#cell_marks').val("1");
    }
    if (range !== undefined) {
        rangetype = range.split('_')[0];
        var rangeval = range.split('_')[1];
        $('#cell_rangeval').val(rangeval);
        $(':radio[value="rangetype_'+rangetype+'"]').attr('checked', true);
    }
    else {
        $(':radio[value="rangetype_'+'AbsoluteRange'+'"]').attr('checked', true);
        $('#cell_rangeval').val("0");
    }
    if (feedback !== undefined) {

        $('#cell_feedback').val(feedback);

    }
    else {
        $('#cell_feedback').val("");
    }

}

function setOptionsOther(){
    //cell is set to None celltype
    $('#lsscontrols_div :radio[name=cell_type]').removeAttr("disabled");

}
function setOptionsCalcAnswer(){
    $('#lsscontrols_div :radio').removeAttr("disabled");
    $('#lsscontrols_div :text').removeAttr("disabled");
}

