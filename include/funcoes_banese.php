<?
//Código do banco de acordo com o banco central
$codigobanco_sz         = "47"; // Sem o zero
$codigobanco 			= "0".$codigobanco_sz; 
$codigo_banco_com_dv	= geraCodigoBanco($codigobanco);
$nummoeda 				= "9";
$fator_vencimento 		= fator_vencimento($dadosboleto["data_vencimento"]);
$valor 					= formata_numero($dadosboleto["valor_boleto"],10,0,"valor");
$agencia 				= formata_numero($dadosboleto["agencia"],2,0);
$conta_cedente 			= formata_numero($dadosboleto["conta"],9,0);
$nnum 					= formata_numero($dadosboleto["nosso_numero"],8,0);
//Calcula o digito verificador do nosso número
$dv_nosso_numero 		= digitoVerificador_nossonumero($nnum,$agencia);
$nossonumero_dv  		= "$nnum$dv_nosso_numero";

// ASBACE
/*
A A C C C C C C C C C N N N N N N N N N B B B D D 
A	- agência do cedente (2 posições)
C	- conta corrente do cedente (9 posições)
N	- nosso número, incluindo o dígito (9 posições)
B	- código do banco, 047 para o Banese (3 posições)
D	- duplo dígito (2 posições)

CHAVE ASBACE: 34.031028084.000010876.047-86 BANESE

CHAVE ASBACE:  34.031028084.000010876.047-86     BANESE 


CHAVE ASBACE: 34.031028084.000108760.047-92     BANESE 

*/

$asbace = $agencia.$conta_cedente.$nossonumero_dv.$codigobanco;
$dv1 = digitoVerificador_asbace1($asbace);
$dv2 = digitoVerificador_asbace2($asbace,$dv1);
$dv3 = digitoVerificador_asbace3($asbace,$dv1);
$asbace = $asbace.$dv3.$dv2;
$asbace_formatada = substr($asbace,0,2).".".substr($asbace,2,9).".".substr($asbace,11,9).".".substr($asbace,20,3)."-".substr($asbace,23,2);


// 43 numeros para o calculo do digito verificador
$dv = digitoVerificador("$codigobanco$nummoeda$fator_vencimento$valor$asbace");
$barras = "$codigobanco$nummoeda$dv$fator_vencimento$valor$asbace"; 

$linha = $codigobanco.$nummoeda.$asbace.$fator_vencimento.$valor;
$linha_formatada = monta_linha_digitavel($linha,$barras);

$dadosboleto["codigo_barras"]       = $barras;
$dadosboleto["linha_digitavel"]     = $linha_formatada;
$dadosboleto["agencia_codigo"]      = $agencia_codigo;
$dadosboleto["nosso_numero"]        = $nnum;
$dadosboleto["codigo_banco_com_dv"] = $codigo_banco_com_dv;

function digitoVerificador($numero) {
    $digito = modulo_11($numero);
    if (in_array((int)$digito,array(0,1,10,11))) {
        $digito = 1;
	}
    return $digito;
}

function monta_linha_digitavel($linha,$barras) {
			
/*
BBBMC.CCCCd CCCCC.CCCCCd CCCCC.CCCCCd D FFFFVVVVVVVVVV
onde:
• B – número do banco
• M – moeda
• C – campo livre (Chave ASBACE)
• d – dígito verificador dos campos 1, 2 e 3
• D – dígito verificador geral
• F – fator de vencimento
• V – valor
• 2 posições para dígitos verificadores (duplo dígito da chave ASBACE).
*/


        // 1. Campo - composto pelo código do banco, código da moéda, as cinco primeiras posições
        // do campo livre e DV (modulo10) deste campo
        $p1 = substr($linha, 0, 5);
        $p2 = substr($linha, 5, 4);
        $p3 = modulo_10("$p1$p2");
        $campo1 = "$p1.$p2$p3";

        // 2. Campo - composto pelas posiçoes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 9, 5);
        $p2 = substr($linha, 14, 5);
        $p3 = modulo_10("$p1$p2");
        $campo2 = "$p1.$p2$p3";

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 19, 5);
        $p2 = substr($linha, 24, 5);
        $p3 = modulo_10("$p1$p2");
        $campo3 = "$p1.$p2$p3";

        // 4. Campo - digito verificador do codigo de barras
		// considerando os 43 dígitos que compõem o código de barras, já excluída a 5ª posição
        $p1 = substr($barras, 0, 4);
        $p2 = substr($barras, 5, 39);		
		$campo4 = modulo_11($p1.$p2);

        // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
		$p1 = substr($linha, 29, 14);
		$campo5 = "$p1";

        return "$campo1 $campo2 $campo3 $campo4 $campo5"; 
}

function modulo_10($num) { 
		$numtotal10 = 0;
        $fator = 2;

        // Separacao dos numeros
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = substr($num,$i-1,1);
            // Efetua multiplicacao do numero pelo (falor 10)
            // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do Itaú
            $temp = $numeros[$i] * $fator; 
            $temp0=0;
            foreach (preg_split('//',$temp,-1,PREG_SPLIT_NO_EMPTY) as $k=>$v){ $temp0+=$v; }
            $parcial10[$i] = $temp0; //$numeros[$i] * $fator;
            // monta sequencia para soma dos digitos no (modulo 10)
            $numtotal10 += $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2; // intercala fator de multiplicacao (modulo 10)
            }
        }
		
        // várias linhas removidas, vide função original
        // Calculo do modulo 10
        $resto = $numtotal10 % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }
		
        return $digito;
		
}

function digitoVerificador_asbace1($numero) {
	$numext = $numero;
	$n = 1;
	
	for ($i = 1; $i <= strlen($numext); $i++) {
		$numeros[$i] = substr($numext,$i-1,1);
		if ($n == 2){
			$n--;
		} else {
			$n++;		
		}
		$p = $numeros[$i]*$n;
		if($p > 9){ 
			$s = $p - 9;
		}
		if($p < 10){
			 $s = $p;
		}
		$z += $s;		
		
	}
	$resto = $z % 10;
    if ($resto == 0) {
		$dv1 = 0;
	}else{
		$dv1 = 10 - $resto;
	} 

	return $dv1;	
}

function digitoVerificador_asbace2($numero,$dv1) {	
	$numext = $numero.$dv1;	
	$z = 0;
	$n = 8;
	for ($i = 1; $i <= strlen($numext); $i++) {
		$numeros[$i] = substr($numext,$i-1,1);
		if ($n == 2){
			$n = 7;
		} else {
			$n--;		
		}
		$p = $numeros[$i]*$n;
		$z += $p;		
		
	}
	
	$resto = $z % 11;
    if ($resto == 0) {
		$dv2 = 0;
	} elseif($resto == 1) {
	
		if($dv1 < 9) {
		      $dv1 = $dv1 + 1;
			  $dv2 = digitoVerificador_asbace2($numero,$dv1);
		} else {
			  $dv2 = digitoVerificador_asbace2($numero,0);
		}
	
	} elseif($resto > 1) {
		$dv2 = 11 - $resto;
	}		 
	//echo "<br> ----> ".$dv2;				
	
	return $dv2;
}

function digitoVerificador_asbace3($numero,$dv1) {	
	$numext = $numero.$dv1;	
	$z = 0;
	$n = 8;
	for ($i = 1; $i <= strlen($numext); $i++) {
		$numeros[$i] = substr($numext,$i-1,1);
		if ($n == 2){
			$n = 7;
		} else {
			$n--;		
		}
		$p = $numeros[$i]*$n;
		$z += $p;		
		
		//echo "<br>".$numeros[$i]." * $n = $z";	
	}
	
	$resto = $z % 11;
    if ($resto == 0) {
		$dv2 = 0;
	} elseif($resto == 1) {
	
		if($dv1 < 9) {
		      $dv1 = $dv1 + 1;
			  $dv2 = digitoVerificador_asbace2($numero,$dv1);
		} else {
			  $dv2 = digitoVerificador_asbace2($numero,0);
		}
	
	} elseif($resto > 1) {
		$dv2 = 11 - $resto;
	}		 
	
	return $dv1;
}

function geraCodigoBanco($numero) {
    $parte1 = substr($numero, 0, 3);
    $parte2 = modulo_11($parte1);
    return $parte1 . "-" . $parte2;
}

function modulo_11($num, $base=9)  {                                      

    $soma = 0;
    $fator = 2;

    /* Separacao dos numeros */
    for ($i = strlen($num); $i > 0; $i--) {
        // pega cada numero isoladamente
        $numeros[$i] = substr($num,$i-1,1);
        // Efetua multiplicacao do numero pelo falor
        $parcial[$i] = $numeros[$i] * $fator;
        // Soma dos digitos
        $soma += $parcial[$i];
        if ($fator == $base) {
            // restaura fator de multiplicacao para 2 
            $fator = 1;
        }
        $fator++;
    }

	$resto = $soma % 11;
	if($resto < 2){
	 	$dv = 1;
	} else {
		$dv = 11 - $resto;
	}
	
	 return $dv;
}

function digitoVerificador_nossonumero($numero,$agencia) {
	$numext = "0".$agencia.$numero;
    $resul = 0;
	$n = 1;
	for ($i = strlen($numext); $i > 0; $i--) {
		$numeros[$i] = substr($numext,$i-1,1);
		if ($n == 9){
			$n = 2;
		} else {
			$n++;		
		}		
		$resul += $numeros[$i]*$n;
	}
	$resto = $resul % 11;
    if ($resto == 0 || $resto == 1) $dv=0;
	else $dv = 11 - $resto; 
	return $dv;
}

function formata_numero($numero,$loop,$insert,$tipo = "geral") {
	if ($tipo == "geral") {
		$numero = str_replace(",","",$numero);
		while(strlen($numero)<$loop){
			$numero = $insert . $numero;
		}
	}
	if ($tipo == "valor") {
		$numero = str_replace(",","",$numero);
		while(strlen($numero)<$loop){
			$numero = $insert . $numero;
		}
	}
	if ($tipo = "convenio") {
		while(strlen($numero)<$loop){
			$numero = $numero . $insert;
		}
	}
	return $numero;
}

function fator_vencimento($data) {
	$data = split("/",$data);
	$ano = $data[2];
	$mes = $data[1];
	$dia = $data[0];
    return(abs((_dateToDays("1997","10","07")) - (_dateToDays($ano, $mes, $dia))));
}

function _dateToDays($year,$month,$day) {
    $century = substr($year, 0, 2);
    $year = substr($year, 2, 2);
    if ($month > 2) {
        $month -= 3;
    } else {
        $month += 9;
        if ($year) {
            $year--;
        } else {
            $year = 99;
            $century --;
        }
    }
    return ( floor((  146097 * $century)    /  4 ) +
            floor(( 1461 * $year)        /  4 ) +
            floor(( 153 * $month +  2) /  5 ) +
                $day +  1721119);
}

function fbarcode($valor){

$fino = 1 ;
$largo = 3 ;
$altura = 50 ;

  $barcodes[0] = "00110" ;
  $barcodes[1] = "10001" ;
  $barcodes[2] = "01001" ;
  $barcodes[3] = "11000" ;
  $barcodes[4] = "00101" ;
  $barcodes[5] = "10100" ;
  $barcodes[6] = "01100" ;
  $barcodes[7] = "00011" ;
  $barcodes[8] = "10010" ;
  $barcodes[9] = "01010" ;
  for($f1=9;$f1>=0;$f1--){ 
    for($f2=9;$f2>=0;$f2--){  
      $f = ($f1 * 10) + $f2 ;
      $texto = "" ;
      for($i=1;$i<6;$i++){ 
        $texto .=  substr($barcodes[$f1],($i-1),1) . substr($barcodes[$f2],($i-1),1);
      }
      $barcodes[$f] = $texto;
    }
  }


//Desenho da barra


//Guarda inicial
?><img src=imagens/p.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/p.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
<?php
$texto = $valor ;
if((strlen($texto) % 2) <> 0){
	$texto = "0" . $texto;
}

// Draw dos dados
while (strlen($texto) > 0) {
  $i = round(esquerda($texto,2));
  $texto = direita($texto,strlen($texto)-2);
  $f = $barcodes[$i];
  for($i=1;$i<11;$i+=2){
    if (substr($f,($i-1),1) == "0") {
      $f1 = $fino ;
    }else{
      $f1 = $largo ;
    }
?>
    src=imagens/p.png width=<?php echo $f1?> height=<?php echo $altura?> border=0><img 
<?php
    if (substr($f,$i,1) == "0") {
      $f2 = $fino ;
    }else{
      $f2 = $largo ;
    }
?>
    src=imagens/b.png width=<?php echo $f2?> height=<?php echo $altura?> border=0><img 
<?php
  }
}

// Draw guarda final
?>
src=imagens/p.png width=<?php echo $largo?> height=<?php echo $altura?> border=0><img 
src=imagens/b.png width=<?php echo $fino?> height=<?php echo $altura?> border=0><img 
src=imagens/p.png width=<?php echo 1?> height=<?php echo $altura?> border=0> 
  <?php
} //Fim da função

function esquerda($entra,$comp){
	return substr($entra,0,$comp);
}

function direita($entra,$comp){
	return substr($entra,strlen($entra)-$comp,$comp);
}


?>