document.addEventListener('DOMContentLoaded', function() {
    
    // Copy to clipboard
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const textToCopy = this.dataset.copy;
            navigator.clipboard.writeText(textToCopy);
            
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check-lg"></i> Copiado!';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });
    
    // Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja excluir este item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Format currency input
    const currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2);
            this.value = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        });
    });

    // Função CRC16 para geração de PIX
function crc16Pix(str) {
    let crc = 0xFFFF;
    for (let i = 0; i < str.length; i++) {
        crc ^= str.charCodeAt(i) << 8;
        for (let j = 0; j < 8; j++) {
            crc = (crc & 0x8000) ? (crc << 1) ^ 0x1021 : crc << 1;
        }
    }
    return crc & 0xFFFF;
}

// Gerar código PIX completo
function gerarCodigoPix(chave, nome, cidade, valor) {
    const chaveLimpa = chave.replace(/\D/g, '');
    const valorFormatado = parseFloat(valor).toFixed(2);
    
    let payload = "000201";
    payload += "010211";
    payload += "26360014br.gov.bcb.pix";
    payload += "01" + String(chaveLimpa.length).padStart(2, '0') + chaveLimpa;
    payload += "52040000";
    payload += "5303986";
    payload += "54" + String(valorFormatado.length).padStart(2, '0') + valorFormatado;
    payload += "5802BR";
    payload += "59" + String(nome.length).padStart(2, '0') + nome;
    payload += "60" + String(cidade.length).padStart(2, '0') + cidade;
    payload += "62070503***";
    payload += "6304";
    
    const crc = crc16Pix(payload);
    payload += crc.toString(16).toUpperCase().padStart(4, '0');
    
    return payload;
}
});