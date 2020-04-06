(function () {
  let html = `
    <div id="media-composer" class="media-composer" v-bind:class="{ loading: loading }">
      <input type="file" accept=".jpg, .jpeg, .png" multiple class="media-manager__load-files">
      <button v-on:click="loadFiles">Загрузить файлы</button>
      <div class="media-composer__toolbar">
        <button v-on:click.prevent="createFolder" class="btn btn--outline media-composer__tool-btn">Создать папку</button>
      </div>
      <div class="media-composer__files">
        <div v-if="directory !== '/'"
             class="media-manager-item media-manager-item--back"
             v-on:dblclick="backDirectory()">
          <img src="/image/admin/media-manager/folder.png">
          <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="459px" height="459px" viewBox="0 0 459 459" style="enable-background:new 0 0 459 459;" xml:space="preserve">
          <g><g id="reply"><path d="M178.5,140.25v-102L0,216.75l178.5,178.5V290.7c127.5,0,216.75,40.8,280.5,130.05C433.5,293.25,357,165.75,178.5,140.25z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
        </div>

        <div v-for="(file, fileIndex) in files" v-bind:data-url="file.url"
             class="media-composer-item"
             v-bind:class="{
                'media-manager-item--file': file.type === 'file',
                active: file.active
             }"
             v-on:dblclick="selectFile(file)"
        >
          <div class="media-composer-item__actions">
            <a href="#" v-on:click.prevent="renameFile(file)" class="mm-action-btn mm-action-btn--rename" title="Переименовать"></a>
            <a href="#" v-on:click.prevent="deleteFile(file, fileIndex)" class="mm-action-btn mm-action-btn--delete" title="Удалить"></a>
          </div>
          <img v-bind:src="file.type === 'directory' ? '/image/admin/media-manager/folder.png' : file.preview">
          <div class="media-composer-item__name">{{ file.name }}</div>
        </div>

      </div>
    </div>
  `;

  let initMM = function (options) {
    let $vue = new Vue({
      el: '#media-composer',
      data: {
        loaded: false,
        loading: false,
        directory: MediaComposer.path,
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

          axios.get('/media-manager/getImages', {
            params: {
              directory: dir
            }
          })
            .then(function (response) {
              let files = response.data.files;

              for (let i = 0; i < files.length; i++) {
                files[i].active = false;
              }

              $mm.directory = dir;
              $mm.files = files;
              $mm.loading = false;
            });

          this.loaded = true;
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

          axios.post('/admin/media-manager/upload', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
            .then(function (response) {

              if (response.data.success) {
                $mm.load($mm.directory);
                $file.value = '';
              }
              else {
                $mm.loading = false;
                alert(response.data.data.message);
              }

            });
        },

        createFolder: function () {
          let $mm = this,
            folderName = prompt("Введите название папки", 'Новая папка');

          if (folderName) {
            let formData = new FormData();

            formData.append('name', folderName);
            formData.append('directory', this.directory);

            axios.post('/admin/media-manager/createDirectory', formData, {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            })
              .then(function (response) {

                if (response.data.success) {
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

          axios.post('/admin/media-manager/delete', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
            .then(function (response) {
              if (response.data.success) {
                mm.files.splice(fileIndex, 1);
              }

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

          axios.post('/admin/media-manager/rename', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })
            .then(function (response) {
              file.base_name = newName;
              file.name = newName + (file.extension ? '.' + file.extension : '');
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

            MediaComposer.close();
          }
        },

        changeDirectory: function () {
          let path = '/' + this.directoryTree.join('/');

          MediaComposer.path = path;

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

  window.MediaManager = {
    open: function (options) {
      if ( ! this.path) {
        this.path = '/';
      }

      this.$modal = IdeaModal.makeHTML(html, {
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
})();