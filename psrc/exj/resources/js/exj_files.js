Exj.files.charsMaxNameFile = 30; // luego se carga del servidor
Exj.files.maxSizeUpload = 300000; // luego se carga del servidor

Exj.files.isValidFileUpload = function (file) {
    if (!file || (file.size === undefined)) {
        var nameNavegator = Exj.browser.getNameCurrent();

        if (nameNavegator) {
            Exj.moe('Se permite subir archivos con el navegador:<br/>' + nameNavegator + '.<br/>Cargue el archivo con Mozilla Firefox.', 'Error cargando archivo...');
        } else {
            Exj.moe('No se permite subir archivos con este navegador.<br/>Cargue el archivo con Mozilla Firefox.', 'Cargando archivo...');
        }
        return false;
    }
    /*
     if(file.size <= 0){
     Exj.moi('El archivo:<br/>'+file.name+'<br/>está vacío!'+file.size, 'File loading...');
     return false;
     }
     */

    if (file.size > Exj.files.maxSizeUpload) {
        Exj.moi('El archivo:<br/>' + file.name + '<br/>Tiene un tamaño de: ' + Exj.files.renderSize(file.size) + '<br/>El máximo tamaño es: ' + Exj.files.renderSize(Exj.files.maxSizeUpload), 'Cargando archivo...');
        return false;
    }

    return true;
};

Exj.files.exts = {
    _exts: null,
    _addTypeFile: function (name, desc, isSupportedUpload) {
        if (isSupportedUpload === undefined) {
            isSupportedUpload = false;
        }
        if (!desc) {
            desc = name.toUpperCase();
        }

        if (!this._exts) {
            this._exts = new Array();
        }

        this._exts.push({
            name: name,
            desc: desc,
            isSupportedUpload: isSupportedUpload
        });

        return this;
    },
    _init: function () {
        this._addTypeFile('exe', 'Executable');
        this._addTypeFile('txt', 'Text', true);
        this._addTypeFile('pdf', 'Document PDF', true);
        this._addTypeFile('xls', 'Excel 97/2003', true);
        this._addTypeFile('xlsx', 'Excel 97/2000/XP', true);
        this._addTypeFile('ods', 'Hoja de cálculo de OpenDocument', true);
        this._addTypeFile('bat', 'Ejecutable por lote');
        this._addTypeFile('dll', 'Librería dinámica');
        this._addTypeFile('lnk', 'Acceso Directo');
        // this._addTypeFile('zip', 'Zipeado ZIP');
        this._addTypeFile('rar', 'Zipeado RAR');
        this._addTypeFile('sql')._addTypeFile('jar')._addTypeFile('php');
    },
    isSupportedUpload: function (extFile) {
        if (!extFile) {
            Exj.moe('The file has no extension.', 'UNABLE TO LOAD FILE');
            return false;
        }

        var itemExtFound = null;
        for (var i = 0, itemExt; i < this._exts.length; i++) {
            itemExt = this._exts[i];
            if (itemExt.isSupportedUpload) {
                continue;
            }
            if (itemExt.name == extFile) {
                itemExtFound = itemExt;
            }
        }

        if (!itemExtFound) {
            return true;
        }


        Exj.moi('El tipo de archivo: <b>' + itemExtFound.desc + '</b> no está permitido cargar.');

        return false;
    },
    getDesc: function (extFile) {
        if (!extFile) {
            return '';
        }

        var txtDesc = '';
        for (var i = 0, itemExt; i < this._exts.length; i++) {
            itemExt = this._exts[i];
            if (itemExt.name == extFile) {
                txtDesc = itemExt.desc;
                break;
            }
        }

        if (!txtDesc) {
            txtDesc = extFile.toUpperCase();
        }

        return txtDesc;
    }
}; // Exj.files.exts
Exj.files.exts._init();

Exj.files.getExtFromNameFile = function (nameFile) {
    if (!nameFile) {
        return '';
    }

    if (nameFile.indexOf('.') <= 0) {
        return '';
    }

    var partes = nameFile.split('.');
    if (partes.length <= 1) {
        return '';
    }
    var lastParte = partes.length - 1;
    var extFile = partes[lastParte];
    extFile = extFile.toLowerCase();

    return (extFile);
};

Exj.files.isSupportedFile = function (nameFile) {
    var extFile = Exj.files.getExtFromNameFile(nameFile);
    if (!Exj.files.exts.isSupportedUpload(extFile)) {
        return false;
    }

    return true;
};

Exj.files.isValidNameFile = function (nameFile) {
    if (!nameFile) {
        Exj.moe('El archivo es requerido');
        return false;
    }

    if (nameFile.length > Exj.files.charsMaxNameFile) {
        var msg = "El nombre del archivo:<br/>";
        msg += nameFile + '<br/>';
        msg += 'Tiene ' + nameFile.length + ' caracteres, ';
        msg += 'se permite como máximo ' + Exj.files.charsMaxNameFile + ' caracteres.<br/>';
        msg += 'Edite el nombre del archivo y reduzca el número de caracteres.';

        Exj.moi(msg);
        return false;
    }

    if (!this.isSupportedFile(nameFile)) {
        return false;
    }

    return true;
};

Exj.files.renderSize = function (sizeBytes) {
    if (!sizeBytes || sizeBytes == '0') {
        return ('0 ' + 'bytes');
    }

    sizeBytes = parseInt(sizeBytes);

    var s = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
    var e = Math.floor(Math.log(sizeBytes) / Math.log(1024));
    return (sizeBytes / Math.pow(1024, Math.floor(e))).toFixed(2) + " " + s[e];
};

Exj.files.onSelected = function (fb, nameFile) {
    if (!Exj.files.isValidNameFile(nameFile)) {
        fb.reset();
        return;
    }

    var files = this.fileInput.dom.files;
    if (!files) {
        Exj.files.isValidFileUpload('');
        fb.reset();
        return;
    }

    if (files.length <= 0) {
        Exj.moe('Could not load file');
        fb.reset();
        return;
    }
    if (files.length > 1) {
        Exj.moi('You can only select a file');
        fb.reset();
        return;
    }

    if (!Exj.files.isValidFileUpload(files[0])) {
        fb.reset();
        return;
    }
};
