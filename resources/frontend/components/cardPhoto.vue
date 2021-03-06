<template lang="pug">
  .card-photo.card-photo--full(
    v-if="view === 'full'"
    :class="{'blocked': blocked, 'shadowed' : shadowed}"  
  )
    .card-photo__loader(
      v-if="blocked"
    )
      loader
    .card-photo__inner
      .card-photo__picture(
        :style="photoStyle"
        @click="showDetails"
      )
      .card-photo__user-info
        .card-photo__pics
          .card-photo__avatar
            img( :src="pathToAvatar", alt="").card-photo__avatar-pic
        .card-photo__data
          h3.card-photo__title {{card.title}}
          .card-photo__social
            likes-and-comments(
              :liked="card['liked_by_user']"
              :comments="card.comments.length"
              :likes="card['total_likes']"
            )
      .card-photo__photo-info
        .card-photo__album-name {{card['album_name']}}

  .card-photo.card-photo_simple-view(v-else-if="view === 'simple'")
    .card-photo__picture-wrap
      .card-photo__simple-picture(
        :style="photoStyle"
      )
      .card-photo__onpicture-data
        likes-and-comments
    .card-photo__user-info
      card-edit-line(
        :title="card.title"
        @onEdit="() => $emit('onEdit', card.id)"
      )
</template>
<script lang="ts">
import Vue from "vue";
import Component from "vue-class-component";
import { Prop } from "vue-property-decorator";
import CardEditLine from "./card-edit-line.vue";
import likesAndComments from "./likesAndComments.vue";
import { namespace } from "vuex-class";
import { BindingHelpers } from "vuex-class/lib/bindings";
import { getPhotoPath } from "helpers/files";
import { UploadedPhotos } from "../store/types";
import loader from "./loader.vue";

const modals: BindingHelpers = namespace("modals");
const photos: BindingHelpers = namespace("photos");

interface Card {
  id: number;
  title: string;
  filename: string;
  user: User;
}

interface User {
  avatar?: string;
}

@Component({
  components: { likesAndComments, CardEditLine, loader },
  name: "CardPhoto"
})
export default class CardPhoto extends Vue {
  @Prop({ default: "full" })
  public view!: string;

  @Prop({ default: "Название фото" })
  public title!: string;

  @Prop({ default: "" })
  public photo!: string;

  @Prop({ default: {} })
  public card!: Card;

  @Prop({ default: false })
  public shadowed!: boolean;

  @modals.Mutation("showModal")
  public showModal;

  @photos.Mutation("setDetailedPhoto")
  public setDetailedPhoto;

  public blocked: boolean = false;

  public photoStyle: object = {
    backgroundImage: `url('${this.fullImgPath}')`
  };

  get fullImgPath(): string {
    return getPhotoPath(this.card.filename, "photos");
  }

  get pathToAvatar() {
    const avatar = this.card.user.avatar;
    const defaultAvatar = require("../assets/img/defaults/avatar.png");
    return Boolean(avatar) ? avatar : defaultAvatar;
  }

  public showDetails() {
    this.setDetailedPhoto(this.card.id);
    this.showModal("photo-details");
    // // this.blocked = true;
    // // this.$emit("onLoading", this.card.id);

    // this.getInfoById(this.card.id);
  }

  public onEditHandler(): void {
    this.showModal("photo-edit");
    this.$emit("onEdit");
  }
}
</script>
<style src="styles/cardPhoto.pcss" lang="scss" scoped></style>