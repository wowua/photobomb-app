<template lang="pug">
  #modal-photo-detail
    modals-item(
      view="blank"
    )
      template(slot="modal-content")
        .inner-content
          slider(
            :imgPath="`/uploads/photos/origin/${photoInfo.filename}`"
            @onNext="slide('next')"
            @onPrev="slide('prev')"
            :nextIsHidden="nextBtnHidden"
            :prevIsHidden="prevBtnHidden"
          )
          .details
            .details__header
              .details__user
                .user
                  .user__avatar
                    avatar(
                      :avatar="photoInfo.user.avatar"
                    )
                  .user__name {{photoInfo.user.name}}
              .details__likes
                likes-button(
                  :amount="photoInfo['total_likes']"
                  :liked="photoInfo['liked_by_user']"
                  :blocked="lockTheLikes"
                  @onLike="handleLike"
                  @onDislike="handleDislike"
                )
            .details__data    
              .details__title {{photoInfo.title}}
              .details__text 
                p {{photoInfo.description}}
          comments(
            :belongsTo="photoInfo"
            :comments="photoInfo.comments"
          )
</template>

<script lang="ts">
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";
import modalsItem from "./modalsItem.vue";
import likesButton from "./likesButton.vue";
import slider from "./slider.vue";
import comments from "./comments.vue";
import { namespace } from "vuex-class";
import { BindingHelpers } from "vuex-class/lib/bindings";
import { PhotosState } from "../store/modules/photos/types.ts";
import { Photo } from "../store/types";
import avatar from "./avatar.vue";

const photos: BindingHelpers = namespace("photos");
const user: BindingHelpers = namespace("user");

@Component({
  name: "ModalsPhotoDetails",
  components: { modalsItem, slider, likesButton, comments, avatar }
})
export default class ModalsPhotoDetails extends Vue {
  @photos.State((state: PhotosState) => state.photoInfo)
  public photoInfo!: Photo;

  @photos.State((state: PhotosState) => state.recentPhotos.data)
  public recentPhotos!: Photo[];

  @photos.Mutation("setDetailedPhoto")
  public setDetailedPhoto;

  @photos.Action("likeIt")
  public likeIt;

  @photos.Action("dislikeIt")
  public dislikeIt;

  public currentItemIndex: number = 0;
  public lockTheLikes: boolean = false;

  public nextBtnHidden = false;
  public prevBtnHidden = false;

  public async handleLike(): Promise<any> {
    this.lockTheLikes = true;
    try {
      await this.likeIt(this.photoInfo.id);
    } catch (error) {
      console.log(error);
    } finally {
      this.lockTheLikes = false;
    }
  }

  public async handleDislike(): Promise<any> {
    this.lockTheLikes = true;
    try {
      await this.dislikeIt(this.photoInfo.id);
    } catch (error) {
      console.log(error);
    } finally {
      this.lockTheLikes = false;
    }
  }

  public makeScroll(value: number): void {
    const targetItemInRecentPhotos = this.recentPhotos[this.currentItemIndex];
    this.setDetailedPhoto(targetItemInRecentPhotos.id);
    this.showHideButtons();
  }

  public showHideButtons() {
    switch (this.currentItemIndex) {
      case 0:
        this.prevBtnHidden = true;
        break;
      case this.recentPhotos.length - 1:
        this.nextBtnHidden = true;
        break;
      default:
        this.prevBtnHidden = false;
        this.nextBtnHidden = false;
    }
  }

  public setCurrentItemsIndex() {
    const itemIndexInArray: number = this.recentPhotos.indexOf(this.photoInfo);
    this.currentItemIndex = itemIndexInArray;
  }

  @Watch("currentItemIndex")
  public onCurrentIndexChanged(value: number): void {
    this.makeScroll(value);
  }

  public slideByKeys(event: KeyboardEvent): void {
    switch (event.keyCode) {
      case 39:
        this.slide("next");
        break;
      case 37:
        this.slide("prev");
        break;
    }
  }

  public slide(direction: string) {
    switch (direction) {
      case "next":
        this.currentItemIndex++;
        break;

      case "prev":
        this.currentItemIndex--;
        break;
    }
  }

  public created() {
    this.setCurrentItemsIndex();
    this.showHideButtons();
  }

  public mounted() {
    document.addEventListener("keydown", this.slideByKeys);
  }

  public destroyed() {
    document.removeEventListener("keydown", this.slideByKeys);
  }
}
</script>


<style lang="pcss" scoped src="styles/modalsPhotoDetails.pcss"></style>
