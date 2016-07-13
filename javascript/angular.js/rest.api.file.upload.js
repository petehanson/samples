// Copyright Up and Running

var dir = angular.module('app.directives');

dir.directive('fileUpload', [function () {

    return {
        restrict: 'E',
        replace: false,
        templateUrl: 'app/templates/fileField.html',

        // parameters for filtering allowed file formats, size of uploaded file, etc.
        scope: {
            label: '@',
            accept: '@',
            maxSize: '@',
            fileType: '@',
            form: '=form',
            file: '=ngModel'
        },

        require: ['ngModel', '^form'],

        // controller which also uses fileReader service for progress bar
        // and custom messages service for printing status messages
        controller: ['$scope', '$upload', '$http', '$filter', 'fileReader', 'messages',
            function ($scope, $upload, $http, $filter, fileReader, messages) {

                var currentFile,
                    allowedFiles = [],
                    allowed = false;

                $scope.uploaded = false;
                $scope.uploading = false;

                // will be triggered on file select, where file will be validated before uploading to a temp folder
                $scope.onFileSelect = function ($files) {
                    if ($files.length > 0) {

                        currentFile = $files[0];

                        // if filesize is limited and smaller than allowed
                        if (currentFile.size <= $scope.maxSize || !$scope.maxSize) {
                            allowed = false;

                            // validate file types
                            if ($scope.fileType) {
                                allowedFiles = $scope.fileType.replace(' ', '').split(',');
                                for (var i = 0; i < allowedFiles.length; i++) {
                                    if (currentFile.type.indexOf(allowedFiles[i]) > -1)
                                        allowed = true;
                                }
                            } else {
                                allowed = true;
                            }

                            // if all validations are successful
                            if (allowed) {

                                // file upload handler, with progress bar,
                                // where REST API was used for storing file (uploads/file url)
                                $scope.uploading = true;
                                $scope.upload = $upload.upload({
                                    url: 'uploads/file',
                                    file: currentFile
                                }).progress(function (evt) {
                                        $scope.form.$invalid = true;
                                        $scope.progress = parseInt(100.0 * evt.loaded / evt.total) + '%';
                                    }).success(function (data, status, headers, config) {
                                        $scope.uploaded = true;
                                        $scope.uploading = false;
                                        if ($scope.form.$valid) $scope.form.$invalid = false;
                                        $scope.file.attrs = data;
                                    });
                            } else {
                                messages.setMessage('File type must be ' + $scope.fileType);
                            }
                        } else {
                            // prints global message with formatted file size (2MB, 2.5KB), using custom bytes filter
                            messages.setMessage('File size is larger then ' + $filter('bytes')($scope.maxSize));
                        }
                    } else {
                        $scope.uploaded = false;
                    }
                };

                // abort upload handler
                $scope.AbortUpload = function () {
                    if (currentFile) {
                        $scope.upload.abort();
                        $scope.uploading = false;
                    }
                };

                // before form is submitted, user can cancel/remove file from a server via REST API
                $scope.RemoveFile = function (name) {
                    if (name) {
                        $http.delete('/uploads/file/' + name)
                            .success(function (data, status, headers, config) {
                                $scope.uploaded = false;
                                $scope.file.attrs = {};
                            })
                            .error(function (data, status, headers, config) {
                                messages.setMessage(data.message, 'error');
                            });
                    } else {
                        $scope.uploaded = false;
                    }
                };

                // for reading uploaded file size, and rest of the file parameters
                $scope.getFile = function (file) {
                    fileReader.readAsDataUrl(file, $scope)
                        .then(function (result) {
                            $scope.file = result;
                        });
                };
            }],

        link: function (scope, elem, attrs, ctrl) {

            // when form is opened in edit, if file is already uploaded
            if (scope.file && scope.file.attrs.name) {
                scope.uploaded = true;
            }
        }
    };
}]);
