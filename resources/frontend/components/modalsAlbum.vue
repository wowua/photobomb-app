<template lang="pug">
  #modal-album-wrapper
    modals-item(
      :title="modalTitle"
      :blocked="formIsBlocked"
    )
      template(slot="modal-content")
        .modal__row
          label.modal__block
            .modal__block-title Название
            .modal__block-control 
              input-rounded(
                placeholder="Введите название альбома"
                v-model="newAlbum.title"
                :error="validation.firstError('newAlbum.title')"
                hideErrorText
              )
        .modal__row
          label.modal__block
            .modal__block-title Описание
            .modal__block-control 
              input-rounded(
                element="textarea"
                v-model="newAlbum.desc"
                :error="validation.firstError('newAlbum.desc')"
                hideErrorText
              )

        .modal__row
          cover-loader(
            @onCoverUploaded="file => newAlbum.cover = file"
            :errorText="validation.firstError('newAlbum.cover')"
            fileRestrictionText="1.5 МБ"
            :cover="albumCoverPreview"
          )

      template(slot="modal-buttons")
        .modal__buttons-common
          .modal__buttons-elem
            button-round(
              text="Сохранить"
              :filled="true"
              @click="options.mode === 'edit' ? editExistedAlbum() : createNewAlbum()"
              :disabled="!!validation.allErrors().length"
            )
          .modal__buttons-elem
            button-round(
              text="Отменить"
              bgClass="transparent"
            )
        .modal__button-right
          button-round(
            text="Удалить"
            v-if="options.mode === 'edit'"
            bgClass="red"
            icon="trash"
            :filled="true"
            @click="removeCurrentAlbum"
          )  
</template>

<script lang="ts">
import SimpleVueValidator, { Validator } from "simple-vue-validator";
import Vue from "vue";
import { mixins } from "vue-class-component";
import { Component, Prop } from "vue-property-decorator";
import { Action, Mutation, namespace } from "vuex-class";
import { renderFile, getPhotoPath } from "../helpers/files";
import { AlbumItem } from "../store/modules/albums/types";
import buttonRound from "./buttonRound.vue";
import inputRounded from "./inputRounded.vue";
import modalsItem from "./modalsItem.vue";
import coverLoader from "./coverLoader.vue";

const albums = namespace("albums");
const modals = namespace("modals");
const alerts = namespace("alerts");

@Component({
  name: "ModalsAlbum",
  validators: {
    "newAlbum.title"(value) {
      return Validator.value(value).required("Заголовок не может быть пустым");
    },
    "newAlbum.desc"(value) {
      return Validator.value(value).required("Описание не может быть пустым");
    },
    "newAlbum.cover"(value) {
      const validate = value =>
        new Promise((resolve, reject) => {
          const checkType = () => {
            if (
              ["image/jpeg", "image/png", "image/jpg"].indexOf(value.type) ===
              -1
            ) {
              resolve("Файл должен быть изображением (jpg, png)");
            }
          };

          const checkSize = () => {
            if (value.size > 1500000) {
              resolve("Файл весит больше чем 1.5 Мб");
            }
          };

          const fileExists = () => Boolean(value);

          const checkFileInEditMode = () => {
            if (fileExists()) {
              checkType();
              checkSize();
            } else {
              resolve(false);
            }
          };

          const checkFileInCreateMode = () => {
            if (fileExists() === false) {
              resolve("У альбома должна быть обложка");
            }
            checkType();
            checkSize();
          };

          if (this.options.mode === "edit") {
            checkFileInEditMode();
          } else {
            checkFileInCreateMode();
          }
        });
      return Validator.custom(async () => {
        return await validate(value);
      });
    }
  },
  components: { inputRounded, buttonRound, modalsItem, coverLoader }
})
export default class ModalsAlbum extends mixins() {
  @albums.Action("createNewAlbum")
  public createAlbumAction: any;

  @modals.Mutation("clearModal")
  public closeModal!: any;

  @alerts.Mutation("showAlerts")
  public showAlerts;

  @albums.State(state => state.currentAlbum)
  public currentAlbum;

  @albums.Action("removeAlbum")
  public removeAlbum;

  @albums.Action("editAlbum")
  public editAlbum;

  @Prop({ default: () => ({}) })
  public options;

  public newAlbum: AlbumItem = {
    id: 0,
    title: "",
    desc: "",
    cover: null
  };

  public formIsBlocked: boolean = false;

  public albumCoverPreview: string = "";

  get modalTitle() {
    return this.options.mode === "edit" ? "Изменить альбом" : "Добавить альбом";
  }

  public clearFormData() {
    const coverElem: HTMLElement = this.$refs.cover as HTMLElement;

    this.newAlbum.id = 0;
    this.newAlbum.title = "";
    this.newAlbum.desc = "";
    this.newAlbum.cover = "";
  }

  public async createNewAlbum() {
    const formData: FormData = new FormData();
    const success = await this.$validate();

    if (!success) {
      return;
    }

    Object.keys(this.newAlbum).forEach(prop => {
      formData.append(prop, this.newAlbum[prop]);
    });

    try {
      const albumCreated = await this.createAlbumAction(formData);
      this.showAlerts({
        type: "success",
        messages: ["Альбом добавлен"]
      });
    } catch (error) {
      this.showAlerts({
        type: "error",
        messages: ["Ошибка. Не удалось добавить альбом"]
      });
    }
    this.closeModal();
  }

  public async removeCurrentAlbum() {
    this.formIsBlocked = true;

    try {
      await this.removeAlbum(this.currentAlbum.id);
      this.showAlerts({
        type: "success",
        messages: ["Альбом удален"]
      });
    } catch (error) {
      this.showAlerts({
        type: "error",
        messages: ["Ошибка. Альбом не удалось добвить"]
      });
    } finally {
      this.closeModal();
      this.formIsBlocked = false;
    }
  }

  public async editExistedAlbum() {
    if ((await this.$validate()) === false) {
      return;
    }

    this.formIsBlocked = true;
    try {
      await this.editAlbum(this.newAlbum);
      this.showAlerts({
        type: "success",
        messages: ["Альбом изменен"]
      });
    } catch (error) {
      this.showAlerts({
        type: "error",
        messages: ["Ошибка во время редактирования"]
      });
    } finally {
      this.closeModal();
      this.formIsBlocked = false;
    }
  }

  public setDefaultValues() {
    const fields = ["id", "title", "desc"];
    const setDefaultField = field =>
      (this.newAlbum[field] = this.currentAlbum[field]);

    fields.forEach(setDefaultField);

    this.albumCoverPreview = getPhotoPath(
      this.currentAlbum.cover,
      "albums_covers"
    );
  }

  public mounted() {
    if (this.options.mode === "edit") {
      this.setDefaultValues();
    }
  }

  public beforeDestroy() {
    this.clearFormData();
  }
}
</script>


<style lang="pcss" scoped src="styles/modals.pcss"></style>
<style lang="pcss" scoped src="styles/modalsAlbum.pcss"></style>
