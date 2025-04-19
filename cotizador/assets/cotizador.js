document.addEventListener('DOMContentLoaded', function() {
    // IDs del formulario
    const ids = ["siteType", "numPages", "design", "payments", "seo", "maintenance"];
    let allExist = ids.every(id => document.getElementById(id));
    if (!allExist || !document.getElementById('sendQuote')) {
        // No ejecutar nada si el formulario no está en la página
        return;
    }
    function calcularCotizacion() {
        let siteType = parseInt(document.getElementById("siteType").value);
        let numPages = parseInt(document.getElementById("numPages").value);
        let design = parseInt(document.getElementById("design").value);
        let payments = parseInt(document.getElementById("payments").value);
        let seo = parseInt(document.getElementById("seo").value);
        let maintenance = parseInt(document.getElementById("maintenance").value);

        let total = siteType;
        if (numPages > 3) {
            total += (numPages - 3) * 500;
        }
        total += design + payments + seo + maintenance;
        document.getElementById("totalAmount").textContent = `$${total.toLocaleString()} MXN`;
    }

    // Listeners para recalcular
    ["siteType", "numPages", "design", "payments", "seo", "maintenance"].forEach(id => {
        document.getElementById(id).addEventListener('change', calcularCotizacion);
        document.getElementById(id).addEventListener('input', calcularCotizacion);
    });
    calcularCotizacion();

    // Envío de la cotización
    document.getElementById('sendQuote').addEventListener('click', function(e) {
        e.preventDefault();
        const data = {
    nombre: document.getElementById('clientName').value,
    correo: document.getElementById('clientEmail').value,
    telefono: document.getElementById('clientPhone').value,
    tipoSitio: document.getElementById('siteType').options[document.getElementById('siteType').selectedIndex].text,
    precioTipoSitio: document.getElementById('siteType').value,
    numPaginas: document.getElementById('numPages').value,
    diseno: document.getElementById('design').options[document.getElementById('design').selectedIndex].text,
    pagos: document.getElementById('payments').options[document.getElementById('payments').selectedIndex].text,
    seo: document.getElementById('seo').options[document.getElementById('seo').selectedIndex].text,
    mantenimiento: document.getElementById('maintenance').options[document.getElementById('maintenance').selectedIndex].text,
    total: document.getElementById('totalAmount').textContent
};

        // Generar HTML de la cotización para PDF
        let cotizacionHTML = `
<div style='font-family: Arial, sans-serif; background: #fff; max-width: 700px; margin: auto; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 30px;'>
    <div style='display:flex;align-items:center;justify-content:space-between;margin-bottom:35px;'>
        <div><img src='https://edenmendez.com/wp-content/uploads/2024/10/Eden-Mendez_preview_rev_2.png' alt='Logo' style='height:60px;'></div>
        <div style='flex:1;text-align:center;'><h2 style='color:#2ECC71;'>Cotización</h2></div>
        <div style='text-align:right;font-size:0.96em;color:#444;'>
            <p style='margin-bottom:0px'><strong>Empresa:</strong> EdenMendez.com</p>
            <p style='margin-bottom:0px'><strong>Correo:</strong> ventas@edenmendez.com</p>
            <p style='margin-bottom:0px'><strong>Teléfono:</strong> 624-166-5588</p>
            <p style='margin-bottom:0px'><strong>Dirección:</strong> Country del Mar, San Jose del Cabo, Baja California Sur, México</p>
        </div>
    </div>
    <div style='margin-bottom:30px;'><h3 style='color:#333;border-bottom:1px solid #eee;padding-bottom:5px;margin-bottom:15px;'>Datos del Cliente</h3>
        <p style='color:#333;margin-bottom:5px'><strong>Nombre:</strong> ${data.nombre}</p>
        <p style='color:#333;margin-bottom:5px'><strong>Correo electrónico:</strong> ${data.correo}</p>
        <p style='color:#333;margin-bottom:5px'><strong>Teléfono:</strong> ${data.telefono}</p>
    </div>
    <table style='width:100%;border-collapse:collapse;margin-bottom:30px;'>
        <thead>
            <tr><th style='color:#333;background:#f0f4f8;border:1px solid #eee;padding:10px;'>Concepto</th><th style='color:#333;background:#f0f4f8;border:1px solid #eee;padding:10px;'>Detalle</th><th style='color:#333;background:#f0f4f8;border:1px solid #eee;padding:10px;'>Precio</th></tr>
        </thead>
        <tbody>
            <tr><td style='color:#333;border:1px solid #eee;padding:10px;'>Tipo de Sitio Web</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.tipoSitio}</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${document.getElementById('siteType').value ? `$${parseInt(document.getElementById('siteType').value).toLocaleString()} MXN` : ''}</td></tr>
            <tr><td style='color:#333;border:1px solid #eee;padding:10px;'>Número de páginas</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.numPaginas}</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${(parseInt(data.numPaginas) - 3) > 0 ? `$${((parseInt(data.numPaginas)-3)*500).toLocaleString()} MXN` : '$0 MXN'}</td></tr>
            <tr><td style='color:#333;border:1px solid #eee;padding:10px;'>Diseño personalizado</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.diseno}</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.diseno.includes('Sí') ? '$3,000 MXN' : '$0 MXN'}</td></tr>
            <tr><td style='color:#333;border:1px solid #eee;padding:10px;'>Pagos en línea</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.pagos}</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.pagos.includes('Sí') ? '$5,000 MXN' : '$0 MXN'}</td></tr>
            <tr><td style='color:#333;border:1px solid #eee;padding:10px;'>SEO</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.seo}</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.seo.includes('Sí') ? '$2,000 MXN' : '$0 MXN'}</td></tr>
            <tr><td style='color:#333;border:1px solid #eee;padding:10px;'>Mantenimiento</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.mantenimiento}</td><td style='color:#333;border:1px solid #eee;padding:10px;'>${data.mantenimiento.includes('Sí') ? '$1,000 MXN' : '$0 MXN'}</td></tr>
        </tbody>
    </table>
    <div style='text-align:right;font-size:1.2em;font-weight:bold;color:#2ECC71;'>Total: ${data.total}</div>
    <div style='text-align:center; color:#888; font-size:0.95em; margin-top:30px;'>Gracias por confiar en nuestros servicios. La cotización es válida por 15 días.<br>Términos y Condiciones: <br> El proyecto comienza con un anticipo del 70% y el saldo se paga antes de la entrega final. Los tiempos de entrega dependen de la entrega puntual de materiales por parte del cliente. Se incluyen dos rondas de revisiones; adicionales se facturarán por separado. El cliente es responsable de cubrir los costos de licencias de terceros. Toda la información proporcionada será tratada de manera confidencial. El soporte y mantenimiento no están incluidos, pero pueden contratarse por separado. Las cancelaciones a mitad del proyecto retendrán el anticipo como compensación.</div>
</div>`;

        // Generar PDF usando jsPDF + html2canvas
        if (window.jspdf && window.html2canvas) {
            // Guardar datos en sessionStorage y abrir la plantilla visual para el cliente
            sessionStorage.setItem('datosCotizacion', JSON.stringify(data));
            window.open('/plantilla-cotizador/', '_blank');

            // Generar PDF oculto y enviarlo al backend
            const jsPDF = window.jspdf.jsPDF;
            const tempDiv = document.createElement('div');
            tempDiv.style.position = 'fixed';
            tempDiv.style.left = '-9999px';
            tempDiv.innerHTML = cotizacionHTML;
            document.body.appendChild(tempDiv);
            window.html2canvas(tempDiv, {scale:2}).then(function(canvas) {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'pt', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const imgProps = pdf.getImageProperties(imgData);
                let pdfWidth = pageWidth - 40;
                let pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                if (pdfHeight > pageHeight - 40) {
                    pdfHeight = pageHeight - 40;
                    pdfWidth = (imgProps.width * pdfHeight) / imgProps.height;
                }
                pdf.addImage(imgData, 'PNG', 20, 20, pdfWidth, pdfHeight);
                document.body.removeChild(tempDiv);
                // Enviar PDF al backend en base64
                const pdfBase64 = pdf.output('datauristring').split(',')[1];
                const endpoint = 'https://edenmendez.com/wp-json/cotizador/v1/enviar';
                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ...data,
                        pdf: pdfBase64
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if(res.success){
                        alert('Cotización enviada correctamente.');
                    }else{
                        alert('Hubo un problema al enviar la cotización.');
                    }
                })
                .catch(()=>{
                    alert('Error de conexión al enviar la cotización.');
                });
            });
        } else {
            document.getElementById('cotizador-mensaje').textContent = 'No se pudo cargar la librería para generar PDF.';
        }
    });
});