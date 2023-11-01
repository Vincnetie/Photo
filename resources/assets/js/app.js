import Dropzone from 'dropzone';

document.addEventListener('DOMContentLoaded', function() {
    // Проверяем текущий маршрут
    if (window.location.pathname === '/upload') {
        var myDropzone = new Dropzone("#myDropzone", {
            url: "/upload", // Замените на URL, куда вы хотите отправлять загруженные файлы
            autoProcessQueue: false, // Отключение автоматической загрузки файлов
            maxFilesize: 2, // Максимальный размер файла в мегабайтах
            acceptedFiles: ".jpg,.png,.gif", // Принимаемые типы файлов
            dictDefaultMessage: "Перетащите сюда файлы для загрузки",
            addRemoveLinks: true // Отображение ссылок для удаления загруженных файлов
        });

        // // Обработка успешной загрузки файла
        // myDropzone.on("success", function(file, response) {
        //     console.log("Файл успешно загружен:", response);
        // });
        //
        // // Обработка удаления файла
        // myDropzone.on("removedfile", function(file) {
        //     console.log("Файл удален:", file);
        // });

        // Обработка события нажатия на кнопку отправки формы
        document.querySelector("#submitBtn").addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            myDropzone.processQueue(); // Запуск процесса загрузки файлов
        });
    }
});

require('bootstrap');
