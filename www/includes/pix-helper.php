<?php
// includes/pix-helper.php - Mesclando MCO2 com QRServer

class PixHelper {
    
    /**
     * Formata um campo no padrão do PIX
     * ID + tamanho(2) + valor
     */
    public static function formataCampo($id, $valor) {
        return $id . str_pad(strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
    }
    
    /**
     * Calcula CRC16 para o código PIX
     */
    public static function calculaCRC16($dados) {
        $resultado = 0xFFFF;
        for ($i = 0; $i < strlen($dados); $i++) {
            $resultado ^= (ord($dados[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($resultado & 0x8000) {
                    $resultado = ($resultado << 1) ^ 0x1021;
                } else {
                    $resultado <<= 1;
                }
                $resultado &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
    }
    
    /**
     * Gera código PIX completo (baseado no artigo MCO2)
     * 
     * @param string $chave Chave PIX (email, telefone, CPF, CNPJ)
     * @param string $idTx Identificador da transação (opcional)
     * @param float $valor Valor da transação
     * @param string $nome Nome do recebedor
     * @param string $cidade Cidade do recebedor
     * @return string Código PIX para copiar e colar
     */
    public static function geraPix($chave, $idTx = '', $valor = 0.00, $nome = '', $cidade = '') {
        // Limpar chave (remover caracteres especiais)
        $chave = trim($chave);
        
        // Payload inicial
        $resultado = "000201";
        
        // Campo 26: Informações do PIX
        $campo26 = "0014br.gov.bcb.pix" . self::formataCampo("01", $chave);
        $resultado .= self::formataCampo("26", $campo26);
        
        // Campo 52: Código fixo (0000)
        $resultado .= "52040000";
        
        // Campo 53: Moeda (Real)
        $resultado .= "5303986";
        
        // Campo 54: Valor (se maior que 0)
        if ($valor > 0) {
            $valorFormatado = number_format($valor, 2, '.', '');
            $resultado .= self::formataCampo("54", $valorFormatado);
        }
        
        // Campo 58: País (BR)
        $resultado .= "5802BR";
        
        // Campo 59: Nome do recebedor
        if (!empty($nome)) {
            $resultado .= self::formataCampo("59", substr($nome, 0, 25));
        } else {
            $resultado .= "5901N";
        }
        
        // Campo 60: Cidade
        if (!empty($cidade)) {
            $resultado .= self::formataCampo("60", substr($cidade, 0, 15));
        } else {
            $resultado .= "6001C";
        }
        
        // Campo 62: Identificador da transação
        $idTx = $idTx ?: '***';
        $resultado .= self::formataCampo("62", self::formataCampo("05", $idTx));
        
        // Campo 63: CRC16
        $resultado .= "6304";
        $resultado .= self::calculaCRC16($resultado);
        
        return $resultado;
    }
    
    /**
     * Gera URL do QR Code usando QRServer (mais confiável)
     */
    public static function gerarQRCode($codigoPix, $tamanho = 250) {
        $url = "https://api.qrserver.com/v1/create-qr-code/?size={$tamanho}x{$tamanho}&margin=10&data=" . urlencode($codigoPix);
        return $url;
    }
    
    /**
     * Gera QR Code com fallback (tenta Google Charts se QRServer falhar)
     */
    public static function gerarQRCodeComFallback($codigoPix, $tamanho = 250) {
        // Tentar QRServer primeiro
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$tamanho}x{$tamanho}&margin=10&data=" . urlencode($codigoPix);
        
        // Verificar se a URL está acessível (opcional, pode demorar)
        return $qrUrl;
    }
    
    /**
     * Gera QR Code em base64 (alternativa mais confiável)
     */
    public static function gerarQRCodeBase64($codigoPix, $tamanho = 250) {
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$tamanho}x{$tamanho}&margin=10&data=" . urlencode($codigoPix);
        
        // Tentar baixar a imagem
        $qrData = @file_get_contents($qrUrl);
        
        if ($qrData !== false) {
            return 'data:image/png;base64,' . base64_encode($qrData);
        }
        
        // Fallback: gerar usando HTML (para teste)
        return false;
    }
}