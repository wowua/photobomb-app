<template lang="pug">
  .cover-component-wrap
    .modal__block-title.cover__left-col
      .cover__thumb(
        ref="cover"
        :style="cover ? presettedCover : {}"
      )
    .modal__block-control.cover__right-col
      .cover__btn
        button-round(
          v-bind="$attrs"
          :text="buttonText"
          purpose="file"
          @change.native="gatherData"
        )
      .cover__desc.error(
        v-if="errorText && errorText.length"
      ) {{errorText}}

      .cover__desc(
        v-else-if="showNotification"
        v-text="`файл должен быть размером менее ${fileRestrictionText}`"
      )
</template>

<script lang="ts">
import Vue from "vue";
import buttonRound from "./buttonRound.vue";
import { Component, Prop } from "vue-property-decorator";
import { renderFile } from "../helpers/files";

interface HTMLInputEvent extends Event {
  target: HTMLInputElement & EventTarget;
}

@Component({
  name: "CoverLoader",
  inheritAttrs: false,
  components: {
    buttonRound
  }
})
export default class CoverLoader extends Vue {

  public showNotification: boolean = true;

  @Prop()
  public errorText!: string;

  @Prop({default: "1 МБ"})
  public fileRestrictionText!: string;

  @Prop({default: "Загрузить обложку"})
  public buttonText!: string;

  @Prop({default: ""})
  public cover!: string;

  get presettedCover() {
    return {
      backgroundImage: `url('${this.cover}')`
    }
  }

  public gatherData(e: HTMLInputEvent) {
    const files: FileList = e.target.files as FileList;
    if (!files.length) {
      return;
    }
    const file: File = files[0];
    const cover: HTMLElement = this.$refs.cover as HTMLElement;

    this.$emit("onCoverUploaded", file);

    this.showNotification = false;
    renderFile(file, cover);
  }
}
</script>

<style lang="pcss" scoped src="styles/modals.pcss"></style>
<style lang="pcss" scoped src="styles/coverLoader.pcss"></style>

