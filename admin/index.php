<style>
#content {
	width: 95%;
}

#accordion .ui-accordion-header {
	background: silver;
	padding: 10px;
	box-shadow: 2px 2px 5px #707070;
}
#accordion .ui-accordion-header:hover {
	background: white;
}
#accordion .ui-accordion-content {
	background: #fafafa;
	padding: 14px;
}

#shortcode_table {
  border-collapse: collapse;
}
#shortcode_table td {
	margin: 0;
	border: solid 1px black;
}
#iframe_csv_arq, #iframe_conf, #iframe_ajax{
    width: 0;
    height: 0;
}
.error_msg{
    color: #F00;
    width: 100%;
}
#aba_conf, #aba_graph{
    display: none;
}
#csv_data{
    width: 100%;
    border: 1px solid black;
    border-collapse: collapse;
}
#csv_data td{
    border: 1px dotted black;
}
.tbl-conf{
    text-align: left;
}
.tbl-conf td{
    width: 100px;
}
.tbl-conf label{
    display: inline-block;
    width: 175px;
}
.tbl-conf label.al-right{
    text-align: right;
    width: 115px;
}
.tbl-conf .tbl-conf label.al-right{
    text-align: right;
    width: 100%;
}
.tbl-conf select{
    width: 100%;
}
.tbl-conf input, .tbl-conf select[id=sel_conf]{
    width: 800px;
}
.tbl-conf input[type=submit]{
    width: auto;
}
.tbl-conf .id_var{
    width: 500px;
}
.tbl-conf .id_unid{
    width: 150px;
}
.tbl-conf .id_label{
    width: 130px;
}

.id_var_val, .id_unid_val, .id_label_val{
    display: none;
}

.bg_black{
    position: fixed;
    display: none;
    z-index: 900;
    background: #000;
    opacity: 0.6;
    width: 100%;
    height: 100%;
    top: 0px;
    left: 0px;
}

#bg_black2{
    z-index: 950;
}

#modal_add_var, #modal_add_label, #modal_add_unid{
    position: fixed;
    display: none;
    background: #fff;
    top: 50%;
    left: 50%;
    border-radius: 10px;
    padding: 10px 30px;
}

#modal_add_var{
    width: 600px;
    height: 400px;
    margin-top: -200px;
    margin-left: -300px;
    z-index: 910;
}

#modal_add_label, #modal_add_unid{
    width: 300px;
    height: 200px;
    margin-top: -100px;
    margin-left: -150px;
    z-index: 960;
}

.add-label:hover, .add-unid:hover{
    color: #0074a2;
    cursor: pointer;
}

#codigo_graph{
    width: 1100px;
}
</style>
<div id="content">
	<h1>Criador de Gráficos</h1>

    <div id="accordion">
      <h3 id='aba_dados'>Dados</h3>
      <div>
        <p>
        <form action="admin-post.php?action=csv_upload" method="post" id="frm_csv_arq" target="iframe_csv_arq" enctype="multipart/form-data">
            <label for="csv_arq">Selecione o arquivo:</label>
            <select name="sel_file" id="sel_file">
                <option value="">Selecione...</option>
                <option value="bd">Todos os dados</option>
                <option value="upl">Enviar arquivo novo...</option>
                <?php echo lista_arq(); ?>
            </select>
            <input type="file" name="csv_arq" id="csv_arq" accept=".csv" style="display:none;" disabled />
            <?php wp_nonce_field( plugin_basename( __FILE__ ), 'csv_arq' ); ?>
        </form>
        <div class="error_msg" id="error_msg_csv"></div>
        <iframe src="" class="iframe" id="iframe_csv_arq" name="iframe_csv_arq"></iframe>
        </p>
      </div>
      <h3 id='aba_conf'>Configurações</h3>
      <div>
        <p>
        <form action="admin-post.php?action=sel_graph_conf" method="post" id="frm_graph" target="iframe_conf" enctype="multipart/form-data">
            <input type="hidden" name="dados" id="dados" value="" />
            <table class="tbl-conf">
                <tr>
                    <td><label for="csv_arq">Selecione o gráfico:</label></td>
                    <td>
                        <select name="sel_conf" id="sel_conf">
                        <option value="">Selecione...</option>
                        <?php echo lista_conf(); ?>
                        <option value="novo">Criar novo...</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php wp_nonce_field( plugin_basename( __FILE__ ), 'conf' ); ?>
        </form>
        <div class="error_msg" id="error_msg_conf"></div>
        <iframe src="" id="iframe_conf" name="iframe_conf"></iframe>
        <form action="admin-post.php?action=save_graph_conf" method="post" id="frm_conf" target="iframe_conf" enctype="multipart/form-data">
            <table class="tbl-conf">
                <tr>
                    <td><label for="nome">Título:</label></td>
                    <td colspan="6"><input type="text" name="nome" id="graph_nome" class="conf-data" /></td>
                </tr>
                <tr>
                    <td><label for="complemento_nome">Complemento:</label></td>
                    <td colspan="6"><input type="text" name="complemento_nome" id="graph_complemento_nome" class="conf-data" /></td>
                </tr>
                <tr>
                    <td><label for="tipo">Tipo:</label></td>
                    <td>
                        <select name="tipo" id="graph_tipo" class="conf-data" >
                            <option value="">Selecione...</option>
                            <option>Line</option>
                            <option>Column</option>
                            <option>Bar</option>
                        </select>
                    </td>
                    <td><label for="todos_anos" class="al-right">Mais de 1 ano?</label></td>
                    <td>
                        <select name="todos_anos" id="graph_todos_anos" class="conf-data">
                            <option value="">Selecione...</option>
                            <option>S</option>
                            <option>N</option>
                        </select>
                    </td>
                    <td><label for="id_unid" class="al-right">Unidade:</label></td>
                    <td>
                        <select name="id_unid" id="graph_unid" class="id_unid conf-data">
                            <?php echo lista_unid(); ?>
                        </select>
                        <span class='id_unid_val'></span>
                    </td>
                    <td style="width:12px;">
                        <i class="add-unid dashicons dashicons-plus-alt"></i>
                    </td>
                </tr>
                <tr id="var_title">
                    <td colspan="7"><h2>Variáveis:</h2></td>
                </tr>
                <tr class="conf-edicao" id="tr_add_var">
                    <td colspan="7"><button id="add_var" class="conf-edicao">Adicionar variável</button></td>
                </tr>
                <tr class="conf-edicao">
                    <td colspan="7" style="text-align: right;">
                        <input type="submit" value="Gravar" class="conf-edicao">
                    </td>
                </tr>
            </table>
        </form>
        </p>
      </div>
      <h3 id='aba_graph'>Gráfico</h3>
      <div>
          <div>
              <label for="codigo_graph">Código para gerar esse gráfico: </label>
              <input type="text" readonly name="codigo_graph" id="codigo_graph">
              <input type="button" value="Salvar na lista" id="btn_save_code">
          </div>
          <div id="exibe_grafico"></div>
      </div>
    </div>
    
</div> <!- #content -->
<div class="bg_black" id="bg_black"></div>
<div class="bg_black" id="bg_black2"></div>
<iframe src="" id="iframe_ajax" name="iframe_ajax"></iframe>
<div id="modal_add_var">
    <h1>Adicionar Variável</h1>
    <form id="frm_add_var" action="admin-post.php?action=add_var" method="post" target="iframe_ajax" enctype="multipart/form-data">
        <table>
            <tr>
                <td>Id:</td>
                <td><input type="text" name="id" id="add_var_id" value=""></td>
            </tr>
            <tr>
                <td>Nome:</td>
                <td><input type="text" name="nome" id="add_var_nome" value=""></td>
            </tr>
            <tr>
                <td>Unidade:</td>
                <td>
                    <select name="id_unid" class="id_unid" id="add_var_unid">
                        <?php echo lista_unid(); ?>
                    </select>
                    <span class='id_unid_val'></span>
                    <i class="add-unid dashicons dashicons-plus-alt"></i>
                </td>
            </tr>
            <tr>
                <td>Label:</td>
                <td>
                    <select name="id_label" class="id_label" id="add_var_label">
                        <?php echo lista_label(); ?>
                    </select>
                    <span class='id_label_val'></span>
                    <i class="add-label dashicons dashicons-plus-alt"></i>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" name="submit" value="OK" />
                    <input id="add_var_cancel" type="button" name="cancel" value="Cancelar" />
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="modal_add_label">
    <h1>Adicionar Label</h1>
    <form id="frm_add_label" action="admin-post.php?action=add_label" method="post" target="iframe_ajax" enctype="multipart/form-data">
        <table>
            <tr>
                <td>Label:</td>
                <td><input type="text" name="nome" value=""></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" name="submit" value="OK" />
                    <input id="add_label_cancel" type="button" name="cancel" value="Cancelar" />
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="modal_add_unid">
    <h1>Adicionar Unidade</h1>
    <form id="frm_add_unid" action="admin-post.php?action=add_unid" method="post" target="iframe_ajax" enctype="multipart/form-data">
        <table>
            <tr>
                <td>Unidade:</td>
                <td><input type="text" name="nome" value=""></td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" name="submit" value="OK" />
                    <input id="add_unid_cancel" type="button" name="cancel" value="Cancelar" />
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
var dados_graph = false;
jQuery( document ).ready(function() {
    jQuery( "#accordion" ).accordion({heightStyle: "content"});
    jQuery('#frm_conf').hide().find('.conf-data').attr('disabled', true);
    
    jQuery('#csv_arq').change(function(e){
        jQuery('#frm_csv_arq').attr('action', 'admin-post.php?action=csv_upload').submit();
    });
    jQuery('#sel_file').change(function(e){
        jQuery('#csv_data').remove();
        jQuery('#iframe_csv_arq').hide().css('width', 0).css('height', 0);
        jQuery('#aba_conf, #aba_graph').hide();
        jQuery('#csv_arq').attr('disabled', true).hide();
        if(jQuery(this).val() == '') return;
        if(jQuery(this).val() == 'upl'){
            jQuery('#csv_arq').attr('disabled', false).show();
        }else if(jQuery(this).val() == 'bd'){
            jQuery('#aba_conf').show().click();
            jQuery('#sel_conf').change();
        }else{
            carregaDados();
            jQuery('#aba_conf').show();
            jQuery('#sel_conf').change();
        }
    });
    jQuery('#iframe_csv_arq').load(function(e){
        if(jQuery('#sel_file').val() == 'upl'){
            var data = jQuery('#iframe_csv_arq').contents().text();
            data = jQuery.parseJSON( data );
            if(data.success){
                jQuery('#sel_file').append("<option value='"+data.id+"'>"+data.file.slug+"</option>").val(data.id);
                jQuery('#sel_file').change();
            }else{
                jQuery('#error_msg_csv').html(data.error_msg);
                jQuery('#csv_arq').val('');
                if(data.error == 'duplicado'){
                    jQuery('#sel_file').val(data.file.id);
                    jQuery('#sel_file').change();
                }
            }
        }else{
            jQuery('#frm_csv_arq').append(jQuery(this).contents().find('body').html());
        }
    });
    jQuery('#aba_conf').click(function(e){
        jQuery('#dados').val(jQuery('#sel_file').val());
    });
    jQuery('#sel_conf').change(function(e){
        jQuery('#iframe_conf').hide().css('width', 0).css('height', 0);
        jQuery('#aba_graph').hide();
        jQuery('#frm_conf').hide().find('.conf-data').attr('disabled', true).val('');
        jQuery('.conf-edicao').hide().attr('disabled', true);
        jQuery('.var-container').remove();
        if(jQuery(this).val() == '') return;
        if(jQuery(this).val() == 'novo'){
            jQuery('#frm_conf').show().find('.conf-data').attr('disabled', false);
            jQuery('.conf-edicao').show().attr('disabled', false);
        }else{
            carregaConf();
            jQuery('#aba_graph').show();
            montaGrafico();
        }
    });
    jQuery('#iframe_conf').load(function(e){
        if(jQuery('#sel_conf').val() == '') return;

        var data = jQuery('#iframe_conf').contents().text();
        data = jQuery.parseJSON( data );

        if(jQuery('#sel_conf').val() != 'novo'){
            jQuery('#frm_conf').show();
            jQuery('#graph_nome').val(data.nome);
            jQuery('#graph_complemento_nome').val(data.complemento_nome);
            jQuery('#graph_tipo').val(data.tipo);
            jQuery('#graph_todos_anos').val(data.todos_anos);
            jQuery('#graph_unid').val(data.id_unid);
            jQuery.each(data.variaveis, function(k,v){
                addVar(v);
            });
            jQuery('#frm_conf').show().find('.conf-data').attr('disabled', true);
        }else{
            if(data.success){
                jQuery('#sel_conf')
                    .append('<option value="'+data.id+'">'+data.nome+'</option>')
                    .val(data.id)
                    .change();
            }else{
                alert('Erro ao gravar gráfico!');
            }
        }
    });
    jQuery('#add_var').click(function(e){
        e.preventDefault();
        addVar();
    });
    jQuery('html').delegate('.id_var', 'change', function(e){
        jQuery(this).siblings('.id_var_val').html(jQuery(this).val());
        if(jQuery(this).val() == 'new'){
            modalAddVar(true);
            return;
        }
        var select = jQuery(this);
        jQuery.ajax({
            url: 'admin-post.php?action=dados_var',
            type: 'POST',
            data: 'id='+jQuery(this).val(),
            dataType : 'json'
        }).done(function(dados){
            select.parent().parent().find('.id_unid').val(dados.id_unid).change();
            select.parent().parent().find('.id_label').val(dados.id_label).change();
        });
    });
    jQuery('html').delegate('.id_unid', 'change', function(e){
        jQuery(this).siblings('.id_unid_val').html(jQuery(this).val());
    });
    jQuery('html').delegate('.id_label', 'change', function(e){
        jQuery(this).siblings('.id_label_val').html(jQuery(this).val());
    });
    jQuery('html').delegate('#frm_conf', 'submit', function(e){
        var ret = true;
        if(jQuery('#graph_nome').val() == ''){
            alert('Título do gráfico em branco.');
            jQuery('#graph_nome').focus();
            return false;
        }
        if(jQuery('#graph_tipo').val() == ''){
            alert('Tipo de gráfico em branco.');
            jQuery('#graph_tipo').focus();
            return false;
        }
        if(jQuery('#graph_todos_anos').val() == ''){
            alert('Campo \'mais de 1 ano\' em branco.');
            jQuery('#graph_todos_anos').focus();
            return false;
        }
        if(jQuery('#graph_unid').val() == ''){
            alert('A Unidade não foi selecionada.');
            jQuery('#graph_unid').focus();
            return false;
        }
        if(jQuery('.id_var').length == 0){
            alert('Não existem variáveis para o gráfico.');
            return false;
        }
        jQuery('.id_var').each(function(){
            if(jQuery(this).val() == ''){
                alert('Variável em branco');
                jQuery(this).focus();
                ret = false;
                return false;
            }
        });
        if(!ret) return false;
        jQuery('.id_unid').each(function(){
            if(jQuery(this).val() == ''){
                alert('Escolha a unidade para a variável.');
                jQuery(this).focus();
                ret = false;
                return false;
            }
        });
        if(!ret) return false;
        jQuery('.id_label').each(function(){
            if(jQuery(this).val() == ''){
                alert('Escolha a label para a variável.');
                jQuery(this).focus();
                ret = false;
                return false;
            }
        });
        return ret;
    });
    
    jQuery('html').delegate('.add-unid' ,'click', function(e){
        var div = false;
        jQuery(this).parent().parent().find('.id_unid_val').html('new');
        modalAddTerm('unid', true);
    });

    jQuery('html').delegate('.add-label','click', function(e){
        jQuery(this).parent().parent().find('.id_label_val').html('new');
        modalAddTerm('label', true);
    });

    jQuery('#add_var_cancel').click(function(e){
        var form = jQuery(this).parents('form:first');
        jQuery(form).each(function(){ this.reset(); });
        modalAddVar(false);
        jQuery('.id_var').each(function(){
            if(jQuery(this).val() == 'new') jQuery(this).val(null);
        });
    })

    jQuery('#add_label_cancel').click(function(e){
        var form = jQuery(this).parents('form:first');
        jQuery(form).each(function(){ this.reset(); });
        modalAddTerm('label', false);
    })

    jQuery('#add_unid_cancel').click(function(e){
        var form = jQuery(this).parents('form:first');
        jQuery(form).each(function(){ this.reset(); });
        modalAddTerm('unid', false);
    })

    jQuery('#bg_black').click(function(e){
        if(jQuery('#modal_add_var').is(':visible')) jQuery('#add_var_cancel').click();
    });

    jQuery('#bg_black2').click(function(e){
        if(jQuery('#modal_add_label').is(':visible')) jQuery('#add_label_cancel').click();
        if(jQuery('#modal_add_unid').is(':visible')) jQuery('#add_unid_cancel').click();
    });
    
    jQuery('#frm_add_var').submit(function(e){
        if(jQuery('#add_var_id').val() == ''){
            alert('Id em branco.');
            jQuery('#add_var_id').focus();
            return false;
        }
        if(Number(jQuery('#add_var_id').val()) != jQuery('#add_var_id').val()){
            alert('Coloque apenas números no Id.');
            jQuery('#add_var_id').focus();
            return false;
        }
        if(jQuery('#add_var_nome').val() == ''){
            alert('Nome da variável em branco.');
            jQuery('#add_var_nome').focus();
            return false;
        }
        if(jQuery('#add_var_unid').val() == ''){
            alert('Unidade em branco.');
            jQuery('#add_var_unid').focus();
            return false;
        }
        if(jQuery('#add_var_label').val() == ''){
            alert('Label em branco.');
            jQuery('#add_var_label').focus();
            return false;
        }
    });

    jQuery('#frm_add_label').submit(function(e){
        if(jQuery('#label_nome').val() == ''){
            alert('Label em branco.');
            jQuery('#label_nome').focus();
            return false;
        }
        return true;
    });

    jQuery('#frm_add_unid').submit(function(e){
        if(jQuery('#unid_nome').val() == ''){
            alert('Unidade em branco.');
            jQuery('#unid_nome').focus();
            return false;
        }
        return true;
    });
    
    jQuery('#iframe_ajax').load(function(e){
        var data = jQuery('#iframe_ajax').contents().text();
        data = jQuery.parseJSON( data );
        console.log(data);
        
        switch(data.func){
            case 'add_var':
                if(data.success){
                    jQuery('.id_var_val').each(function(k, div){
                        if(jQuery(this).text() == 'new') jQuery(this).text(data.id);
                    });
                    varComboList();
                    jQuery('#bg_black').click();
                    //alert('Comando executado com sucesso!');
                }else{
                    if(jQuery.type(data.id) !== 'undefined'){
                        if(confirm('Variável já existe!\r\n'+data.id+' - '+data.nome+'\r\nDeseja usá-la?')){
                            jQuery('.id_var_val').each(function(k, div){
                                if(jQuery(this).text() == 'new') jQuery(this).text(data.id);
                            });
                            labelComboList();
                            jQuery('#bg_black').click();
                        }
                    }else{
                        alert('Erro ao salvar Variável!');
                    }
                }
                break;
            case 'add_label':
                if(data.success){
                    jQuery('.id_label_val').each(function(k, div){
                        if(jQuery(this).text() == 'new') jQuery(this).text(data.id);
                    });
                    labelComboList();
                    jQuery('#bg_black2').click();
                    //alert('Label inserida com sucesso!');
                }else{
                    if(jQuery.type(data.id) !== 'undefined'){
                        if(confirm('Label já existe!\r\nDeseja usá-la?')){
                            jQuery('.id_label_val').each(function(k, div){
                                if(jQuery(this).text() == 'new') jQuery(this).text(data.id);
                            });
                            labelComboList();
                            jQuery('#bg_black2').click();
                        }
                    }else{
                        alert('Erro ao incluir Label!');
                    }
                }
                break;
            case 'add_unid':
                if(data.success){
                    jQuery('.id_unid_val').each(function(k, div){
                        if(jQuery(this).text() == 'new') jQuery(this).text(data.id);
                    });
                    unidComboList();
                    jQuery('#bg_black2').click();
                    //alert('Unidade inserida com sucesso!');
                }else{
                    if(jQuery.type(data.id) !== 'undefined'){
                        if(confirm('Unidade já existe!\r\nDeseja usá-la?')){
                            jQuery('.id_unid_val').each(function(k, div){
                                if(jQuery(this).text() == 'new') jQuery(this).text(data.id);
                            });
                            unidComboList();
                            jQuery('#bg_black2').click();
                        }
                    }else{
                        alert('Erro ao incluir Unidade!');
                    }
                }
                break;
            default:
                if(data.success){
                    alert('Comando executado com sucesso!');
                }else{
                    alert('Erro ao executar comando!');
                }
                break;
        }
    });
    
    jQuery('#add_var_id').bind('keydown',soNums);
    
    jQuery('#btn_save_code').click(function(e){
        var codigo = jQuery('#codigo_graph').val();

        var url = 'admin-post.php?action=ins_lista_graph';
        jQuery.ajax({
            url : url,
            data: 'codigo='+codigo,
            type: 'POST',
            dataType : 'json'
        }).done(function(dados){
            if(dados.success){
                alert('Gráfico incluído na lista!');
            }else{
                switch(dados.error){
                    case 'duplicado':
                        alert('Esse gráfico já está na lista!');
                        break;
                    case 'bd':
                        alert("Erro ao gravar no banco de dados!\r\nTente outra vez.");
                        break;
                }
            }
        });
    });
});

function soNums(e){
    //teclas adicionais permitidas (tab,delete,backspace,setas direita e esquerda, shift, end, home, F5)
    var keyCodesPermitidos = new Array(8,9,37,39,46,16,35,36,116);

    //Pega a tecla digitada
    var keyCode = e.which;
     
    if (jQuery.inArray(keyCode,keyCodesPermitidos) != -1){
        return true;
    }
    
    if(e.shiftKey) return false;
     
    //numeros e 0 a 9 do teclado alfanumerico
    for(x=48;x<=57;x++){
        keyCodesPermitidos.push(x);
    }
     
    //numeros e 0 a 9 do teclado numerico
    for(x=96;x<=105;x++){
        keyCodesPermitidos.push(x);
    }
     
    //Verifica se a tecla digitada é permitida
    if (jQuery.inArray(keyCode,keyCodesPermitidos) != -1){
        return true;
    }    
    return false;
}

function carregaDados(){
    jQuery('#frm_csv_arq').attr('action', 'admin-post.php?action=carrega_dados_csv').submit();
}

function carregaConf(){
    if(jQuery('#sel_conf').val() != '' && jQuery('#sel_conf').val() != 'novo')
        jQuery('#frm_graph').attr('action', 'admin-post.php?action=carrega_conf_graph').submit();
}

function addVar(dados){
    if(typeof dados === "undefined"){
        dados = {id_var:'', nome:'', id_unid:'', id_label:''}
    }
    var new_html  = "<tr class='var-container'><td colspan='7'>";
    var new_html = new_html + "<table class='tbl-conf'>";
    var new_html = new_html + "<tr>";
    //var new_html = new_html + "<td><label for='id_var'>Variável:</label></td>";
    var new_html = new_html + "<td><select class='id_var conf-data' name='var[]'></select><span class='id_var_val'>"+dados.id_var+"</span></td>";
    varComboList();
    if(dados.id !== ''){
        var new_html = new_html + "<td><label for='id_unid' class='al-right'>Unidade:</label></td>";
        var new_html = new_html + "<td><select class='id_unid conf-data' name='unid[]'></select><span class='id_unid_val'>"+dados.id_unid+"</span></td><td><i class='add-unid dashicons dashicons-plus-alt'></i></td>";
        unidComboList();
        var new_html = new_html + "<td><label for='id_label' class='al-right'>Label:</label></td>";
        var new_html = new_html + "<td><select class='id_label conf-data' name='label[]'></select><span class='id_label_val'>"+dados.id_label+"</span></td><td><i class='add-label dashicons dashicons-plus-alt'></i></td>";
        labelComboList();
        var new_html = new_html + "</tr>";
    }
    var new_html = new_html + "</table>";
    var new_html = new_html + "</td></tr>";
    
    jQuery(new_html).insertBefore('#tr_add_var');
}

function varComboList(){
    var url = 'admin-post.php?action=lista_var';
    jQuery.ajax({
        url : url,
        data: 'response=json',
        type: 'POST',
        dataType : 'json'
    }).done(function(dados){
        jQuery('.id_var')
                .html('')
                .append('<option value="">Selecione...</option>')
                .append('<option value="new">Criar nova...</option>');
        jQuery.each(dados, function(k,v){
            jQuery('.id_var').append('<option value="'+v.id+'">'+v.id+' - '+v.nome+'</option>');
        });
        jQuery.each(jQuery('.id_var'), function(k,select){
            var valor = jQuery(select).siblings('.id_var_val').text();
            jQuery(select).val(valor).change();
        });
    });
}
function unidComboList(){
    var url = 'admin-post.php?action=lista_unid';
    jQuery.ajax({
        url : url,
        data: 'response=json',
        type: 'POST',
        dataType : 'json'
    }).done(function(dados){
        jQuery('.id_unid').html('').append('<option value="">Selecione...</option>');
        jQuery.each(dados, function(k,v){
            jQuery('.id_unid').append('<option value="'+v.id+'">'+v.nome+'</option>');
        });
        jQuery.each(jQuery('.id_unid'), function(k,select){
            var valor = jQuery(select).siblings('.id_unid_val').text();
            jQuery(select).val(valor);
        });
    });
}
function labelComboList(){
    var url = 'admin-post.php?action=lista_label';
    jQuery.ajax({
        url : url,
        data: 'response=json',
        type: 'POST',
        dataType : 'json'
    }).done(function(dados){
        jQuery('.id_label').html('').append('<option value="">Selecione...</option>');
        jQuery.each(dados, function(k,v){
            jQuery('.id_label').append('<option value="'+v.id+'">'+v.nome+'</option>');
        });
        jQuery.each(jQuery('.id_label'), function(k,select){
            var valor = jQuery(select).siblings('.id_label_val').text();
            jQuery(select).val(valor);
        });
    });
}
function montaGrafico(){
    var url = "admin-post.php?action=monta_graph";
    var dados = {
        'id_graph' : jQuery('#sel_conf').val(),
        'id_arq' : jQuery('#sel_file').val()
    }
    jQuery.ajax({
        url : url,
        data: dados,
        type: 'POST',
        dataType : 'json'
    }).done(function(dados){
        if (dados.success){
            // Create the data table.
            dados_graph = dados;
            google.load("visualization", "1", {packages:["corechart"],'callback': 'drawVisualization(dados_graph)'});
            google.setOnLoadCallback(function() {
                drawVisualization(dados);
            });
        }else{
            
        }
    });
}

function drawVisualization(dados){
    console.log(dados);
    jQuery('#exibe_grafico').html('');
    jQuery.each(dados.localidades, function(id_loc, loc){
        jQuery('#exibe_grafico').append("<div id='graph_"+id_loc+"'></div>");
        var data = new google.visualization.DataTable();
        jQuery.each(dados[id_loc].cols, function(k, col){
            data.addColumn(col.type, col.name);
        });
        jQuery.each(dados.vars, function(k, v){
            var linha = [v.label];
            jQuery.each(dados[id_loc].cols, function(k, col){
                if(col.type == 'number'){
                    if(jQuery.type(dados[id_loc][v.id]) === 'undefined' || jQuery.type(dados[id_loc][v.id][col.name]) === 'undefined')
                        linha.push(null);
                    else
                        linha.push(parseFloat(dados[id_loc][v.id][col.name].valor.replace(',', '.')));
                }
            });
            //console.log(linha);
            data.addRow(linha);
        });

        // Instantiate and draw our chart, passing in some options.
        //console.log(dados.graph.tipo);
        var is3d = false;
        switch(dados.graph.tipo){
            case 'Line':
                var chart = new google.visualization.LineChart(document.getElementById("graph_"+id_loc));
                break;
            case 'Column':
                var chart = new google.visualization.ColumnChart(document.getElementById("graph_"+id_loc));
                break;
            case 'Bar':
                var chart = new google.visualization.BarChart(document.getElementById("graph_"+id_loc));
                break;
            case 'Line3D':
                var chart = new google.visualization.LineChart(document.getElementById("graph_"+id_loc));
                is3d = true;
                break;
            case 'Column3D':
                var chart = new google.visualization.ColumnChart(document.getElementById("graph_"+id_loc));
                is3d = true;
                break;
            case 'Bar3D':
                var chart = new google.visualization.BarChart(document.getElementById("graph_"+id_loc));
                is3d = true;
                break;
        }
        
        // Set chart options
        var options = {'title':loc + "\r\n" + dados.graph.nome,
                       'width':1400,
                       'height':600,
                       'is3D':is3d
        };
        //console.log(options);

        if(jQuery.type(chart) !== 'undefined') chart.draw(data, options);
        
        if(dados.id_arq != "bd" && jQuery.type(dados.id_arq) !== 'undefined'){
            jQuery('#codigo_graph').val('[graph id="'+dados.graph.id+'" arq="'+dados.id_arq+'"]');
        }else{
            jQuery('#codigo_graph').val('[graph id="'+dados.graph.id+'"]');
        }
    });
    jQuery('#aba_graph').click();
}

function modalAddVar(show){
    if(show){
        jQuery('#bg_black').fadeIn();
        jQuery('#modal_add_var').fadeIn();
    }else{
        jQuery('#bg_black').fadeOut();
        jQuery('#modal_add_var').fadeOut();
    }
}

function modalAddTerm(term, show){
    if(show){
        jQuery('#bg_black2').fadeIn();
        jQuery('#modal_add_'+term).fadeIn();
    }else{
        jQuery('#bg_black2').fadeOut();
        jQuery('#modal_add_'+term).fadeOut();
    }
}
</script>
