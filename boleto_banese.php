<?php
// DADOS DO BOLETO PARA O SEU CLIENTE
$data_venc 			= formataData($boleto["vencimento"], 'br', 0);  // Prazo de X dias OU informe data: "13/04/2006"; 
$valorAux 			= str_replace(".", ",",$boleto["valor"]);
$valor_cobrado 		= $valorAux; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado 		= str_replace(",", ".",$valor_cobrado);
$valor_boleto 		= number_format($valor_cobrado, 2, ',', '');

$dadosboleto["nosso_numero"] 		= $boleto["idboleto"];  // Nosso numero sem o DV - REGRA: Máximo de 8 caracteres!
$dadosboleto["numero_documento"] 	= $boleto["idboleto"];	// Num do pedido ou do documento
$dadosboleto["data_vencimento"] 	= $data_venc; //formataData($boleto["vencimento"], 'br', 0); // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] 		= date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] 	= date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] 		= $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
// INSTRUÇÕES PARA O CAIXA
$dadosboleto["instrucoes1"] = "- Após o vencimento cobrar 5% de multa e 0,033% de juros ao dia";
$dadosboleto["instrucoes2"] = "";
$dadosboleto["instrucoes3"] = "";
$dadosboleto["instrucoes4"] = "";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] 		= "1";
$dadosboleto["valor_unitario"] 	= "";
$dadosboleto["aceite"] 			= "";		
$dadosboleto["uso_banco"] 		= ""; 	
$dadosboleto["especie"] 		= "R$";
$dadosboleto["especie_doc"]		= "";



// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - BANESE
$dadosboleto["agencia"] 	= "99"; // ABO 29 Num da agencia, sem digito
//$dadosboleto["tipoconta"] 	= "03"; 	// 01 - Pessoa Fisica         03 - Pessoa Juridica
$dadosboleto["conta"] 		= "999999999"; 	//ABO 031013307 Num da conta, sem digito 03102808-4


// SEUS DADOS
$dadosboleto["identificacao"] 	= "Teste Gera Boloeto";
$dadosboleto["cpf_cnpj"] 		= "";
$dadosboleto["endereco"] 		= "http://banese.com";
$dadosboleto["cidade_uf"] 		= "Aracaju - SE";
$dadosboleto["cedente"] 		= "Teste Gera Boloeto";

// NÃO ALTERAR!
include("include/funcoes_banese.php"); 
include("include/layout_banese.php");
?>
