<template lang="pug">
  .uploader(
    :class="{ blocked: blocked }"
  )
    label.uploader__elem(
      :class="{active: isActiveView, uploaded: anyPicsUploaded}"
      @dragover.prevent="isActiveView = true"
      @dragleave.prevent="isActiveView = false"
      @drop.prevent="handleUpload"
    )
      input(
        type="file"
        multiple="true"
        ref="uploader"
        v-if="anyPicsUploaded === false",
        accept="image/jpeg,image/png,image/jpg"
        @change="handleUpload"
      ).uploader__real
      .uploader__items
        uploader-item(
          v-for="pic in uploadedPhotos"
          :pic="pic.rendered"
          :key="pic.rendered.id"
          @removeItem="removeUploadedPhoto"
        )
      .uploader__desc(v-if="anyPicsUploaded === false") 
        span.uploader__desc-text Перетащите сюда либо #[a.uploader__link выберите фотографии]

    .error-wrapper(v-if="photosWithErrors.length") 
      .error-wrapper__desc Размер файла превышает 1.5 MB
      .uploader__elem.uploader__elem--error
        .uploader__items
          uploader-item(
            v-for="pic in photosWithErrors"
            :pic="pic"
            :key="pic.id"
            hideRemoveBtn
          )

</template>

<script lang="ts">
import uuid from "uuid/v1";
import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
import { renderFile } from "../helpers/files";
import uploaderItem from "./uploaderItem.vue";
import { namespace } from "vuex-class";
import { BindingHelpers } from "vuex-class/lib/bindings";

interface PicData {
  id: number;
  url: string;
}

const photos: BindingHelpers = namespace("uploadedPhotos");

@Component({
  name: "Uploader",
  components: { uploaderItem }
})
export default class Uploader extends Vue {
  public isActiveView: boolean = false;

  public picsToRender: object[] = [];

  public picsLoadedWithErrors: object[] = [];

  @Prop({ default: false })
  public readonly blocked!: boolean;

  @photos.State((state) => state.photosToUpload)
  public uploadedPhotos!: object[];

  @photos.State((state) => state.photosWithErrors)
  public photosWithErrors!: object[];

  @photos.Mutation("addUploadedPhoto")
  public addUploadedPhoto;

  @photos.Mutation("removeUploadedPhoto")
  public removeUploadedPhoto;

  @photos.Mutation("clearBrokenPhoto")
  public clearBrokenPhoto;

  @photos.Mutation("addBrokenPhoto")
  public addBrokenPhoto;

  get anyPicsUploaded(): boolean {
    return this.uploadedPhotos.length !== 0;
  }

  public handleUpload(e): void {
    e.preventDefault();
    const filesObject = e.dataTransfer || e.target;
    const files = filesObject.files;

    this.isActiveView = false;

    this.clearBrokenPhoto();
    this.renderUploadedFiles(files);
  }

  public async renderUploadedFiles(files): Promise<any> {
    // const uploader = this.$refs.uploader as HTMLElement;

    for (const currentFile of files) {
      try {
        const reader = await this.drawPictures(currentFile);
        const overSized = currentFile.size > 1.5 * 1000 * 1000;
        const renderedPhoto: PicData = {
          id: uuid(),
          url: reader.result
        };

        if (overSized) {
          this.addBrokenPhoto(renderedPhoto);
          continue;
        }

        this.addUploadedPhoto({
          original: currentFile,
          rendered: renderedPhoto
        });
      } catch (error) {
        console.log(error);
      }
    }
  }

  public drawPictures(file): Promise<any> {
    const reader = new FileReader();

    reader.readAsDataURL(file);
    return new Promise((resolve, reject) => {
      reader.onloadend = () => {
        resolve(reader);
      };
      reader.onerror = (e) => {
        throw new Error("error");
      };
    });
  }
}
</script>

<style lang="pcss" scoped src="styles/uploader.pcss"></style>
