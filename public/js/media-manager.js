(function () {
  let icons = {

  };

  let html = `
    <div id="media-manager" class="media-manager" v-bind:class="{ loading: loading }">
      <div class="media-manager__top-panel">
        <input type="file" accept=".jpg, .jpeg, .png" multiple class="media-manager__load-files">
        <button v-on:click="loadFiles">Загрузить файлы</button>
        <button v-on:click.prevent="createFolder" class="btn btn--outline media-manager__tool-btn">Создать папку</button>
      </div>
      <div class="media-manager__files">
        <div v-if="directory !== '/'"
             class="media-manager-file"
             v-on:dblclick="backDirectory()">
           <div class="media-manager-file__inner">
              <img class="media-manager-file__dir-img" src="/image/admin/media-manager/folder.png">
              <div class="media-manager-file__dir-name">Назад</div>
           </div>
        </div>

        <div v-for="(file, fileIndex) in files" v-bind:data-url="file.url" class="media-manager-file"
             v-bind:class="{ 'media-manager-file--dir': file.type === 'directory', active: file.active }"
             v-on:dblclick="selectFile(file)">
             <template v-if="'directory' === file.type">
                <div class="media-manager-file__inner">
                  <img class="media-manager-file__dir-img" src="/image/admin/media-manager/folder.png">
                  <div class="media-manager-file__dir-name">{{ file.name }}</div>
                  <div class="media-manager-file__info media-manager-file__info--dir">
                    <div class="media-manager-file__actions">
                      <a href="#" v-on:click.prevent="renameFile(file)" class="media-manager-file__action-btn">Переименовать</a>
                      <a href="#" v-on:click.prevent="deleteFile(file, fileIndex)" class="media-manager-file__action-btn">Удалить</a>
                    </div>
                  </div>
                </div>
             </template>
             <template v-else>
                <div class="media-manager-file__inner">
                  <img class="media-manager-file__preview" v-bind:src="file.preview">
                  <div class="media-manager-file__info">
                    <div class="media-manager-file__name">{{ file.name }}</div>
                    <div class="media-manager-file__actions">
                      <a href="#" v-on:click.prevent="renameFile(file)" class="media-manager-file__action-btn">Переименовать</a>
                      <a href="#" v-on:click.prevent="deleteFile(file, fileIndex)" class="media-manager-file__action-btn">Удалить</a>
                    </div>
                  </div>
                </div>
             </template>
        </div>

      </div>
    </div>
  `;

  let MediaManager = {
    open: function (options) {
      if ( ! this.path) {
        this.path = '/';
      }

      this.$modal = IdeaModal.makeHTML(html, {
        modalClass: 'media-manager-wrap',
        onReady: function (modal) {
          initMM(options);
        }
      });

      this.$modal.show();
    },
    close: function () {
      this.$modal.destroy();
    }
  };

  let initMM = function (options) {
    let $vue = new Vue({
      el: '#media-manager',
      data: {
        loaded: false,
        loading: false,
        directory: MediaManager.path,
        directoryTree: [],
        opened: false,
        files: [],
        onSelect: null
      },
      methods: {
        /**
         * Открывает форму
         */
        open: function (options) {
          if (options && options.onSelect) {
            this.onSelect = options.onSelect;
          }

          if ( ! this.loaded) {
            this.load(this.directory);
          }

          setTimeout(() => {
            this.opened = true;
          }, 50);
        },

        close: function () {
          this.opened = false;
        },

        /**
         * Загрузка изображений
         */
        load: function (dir) {
            let $mm = this;

            this.loading = true;

            IdeaAjax.get('/media-manager/getImages', {
                directory: dir
            })
            .success(function (response) {
                let files = response.data.files;

                for (let i = 0; i < files.length; i++) {
                    files[i].active = false;
                }

                $mm.directory = dir;
                $mm.files = files;
                $mm.loading = false;

                $mm.loaded = true;
            });
        },

        loadFiles: function (event) {
          let $mm = this,
            $file = event.target.parentElement.querySelector('.media-manager__load-files'),
            formData = new FormData();

          if (!$file.files.length) {
            return;
          }

          this.loading = true;

          for (let i = 0; i < $file.files.length; i++) {
            formData.append('file[]', $file.files[i]);
          }

          formData.append('directory', this.directory);

          IdeaAjax.post('/admin/media-manager/upload', formData, {
              'Content-Type': 'multipart/form-data'
          })
          .success(function (response) {
              if (response.isSuccess()) {
                  $mm.load($mm.directory);
                  $file.value = '';

                  if (response.data.errors.length) {
                      alert(response.data.errors.join('\n'));
                  }
              }
          })
          .response(function () {
              $mm.loading = false;
          });
        },

        createFolder: function () {
            let $mm = this,
            folderName = prompt("Введите название папки", 'Новая папка');

            if (folderName) {
                let formData = new FormData();

                formData.append('name', folderName);
                formData.append('directory', this.directory);

                IdeaAjax.post('/admin/media-manager/createDirectory', formData, {
                    'Content-Type': 'multipart/form-data'
                })
                .success(function (response) {
                    if (response.isSuccess()) {
                        $mm.load($mm.directory);
                    }
                });
            }
        },

        openFileActions: function (index) {

          this.files.forEach((file, i) => {
            if (i === index) {
              this.$set(file, 'active', !file.active);
            }
            else {
              this.$set(file, 'active', false);
            }
          });

        },

        deleteFile: function (file, fileIndex) {
            if ( ! confirm('Подтвердите действие')) {
                return;
            }

            let mm = this,
                formData = new FormData();

            this.loading = true;

            formData.append('name', file.name);
            formData.append('directory', this.directory);

            IdeaAjax.post('/admin/media-manager/delete', formData, {
              'Content-Type': 'multipart/form-data'
            })
            .success(function (response) {
                if (response.isSuccess()) {
                    mm.files.splice(fileIndex, 1);
                }


            })
            .response(function () {
                mm.loading = false;
            });
        },

        renameFile: function(file) {
            let newName = prompt('Новое имя', file.base_name);

            if ( ! newName || newName === file.base_name) {
                return;
            }

            let mm = this,
            formData = new FormData();

            this.loading = true;

            formData.append('old_name', file.name);
            formData.append('new_name', newName + (file.extension ? '.' + file.extension : ''));
            formData.append('directory', this.directory);

            IdeaAjax.post('/admin/media-manager/rename', formData, {
              'Content-Type': 'multipart/form-data'
            })
            .success(function (response) {
                file.base_name = newName;
                file.name = newName + (file.extension ? '.' + file.extension : '');
            })
            .response(function () {
                mm.loading = false;
            });
        },

        selectFile: function (file) {
          if (file.type === 'directory') {
            this.directoryTree.push(file.name);
            this.changeDirectory();
          }
          else {
            if (this.onSelect) {
              this.onSelect(file);
              this.onSelect = null;
            }

            MediaManager.close();
          }
        },

        changeDirectory: function () {
          let path = '/' + this.directoryTree.join('/');

          MediaManager.path = path;

          this.load(path);
        },

        backDirectory: function () {
          this.directoryTree.pop();
          this.changeDirectory();
        }
      }
    });

    $vue.open(options);
  };

  ServiceContainer.bindService('media-manager', MediaManager);
})();