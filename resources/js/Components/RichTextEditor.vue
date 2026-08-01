<script setup>
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit,
        Link.configure({ openOnClick: false }),
        Image,
    ],
    editorProps: {
        attributes: { class: 'rich-text-content' },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value, false);
        }
    },
);

function setLink() {
    const url = window.prompt('Link URL');
    if (url === null) return;
    if (url === '') {
        editor.value.chain().focus().unsetLink().run();
        return;
    }
    editor.value.chain().focus().setLink({ href: url }).run();
}

function addImage() {
    const url = window.prompt('Image URL');
    if (url) editor.value.chain().focus().setImage({ src: url }).run();
}
</script>

<template>
    <div class="rounded-md border border-gray-300 dark:border-gray-700">
        <div v-if="editor" class="flex flex-wrap gap-1 border-b border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-800">
            <button type="button" class="rte-btn" :class="{ 'rte-active': editor.isActive('bold') }" @click="editor.chain().focus().toggleBold().run()"><b>B</b></button>
            <button type="button" class="rte-btn" :class="{ 'rte-active': editor.isActive('italic') }" @click="editor.chain().focus().toggleItalic().run()"><i>I</i></button>
            <button type="button" class="rte-btn" :class="{ 'rte-active': editor.isActive('heading', { level: 2 }) }" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()">H2</button>
            <button type="button" class="rte-btn" :class="{ 'rte-active': editor.isActive('heading', { level: 3 }) }" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()">H3</button>
            <button type="button" class="rte-btn" :class="{ 'rte-active': editor.isActive('bulletList') }" @click="editor.chain().focus().toggleBulletList().run()">• List</button>
            <button type="button" class="rte-btn" :class="{ 'rte-active': editor.isActive('orderedList') }" @click="editor.chain().focus().toggleOrderedList().run()">1. List</button>
            <button type="button" class="rte-btn" :class="{ 'rte-active': editor.isActive('blockquote') }" @click="editor.chain().focus().toggleBlockquote().run()">"</button>
            <button type="button" class="rte-btn" @click="setLink">Link</button>
            <button type="button" class="rte-btn" @click="addImage">Image</button>
            <button type="button" class="rte-btn" @click="editor.chain().focus().undo().run()">↺</button>
            <button type="button" class="rte-btn" @click="editor.chain().focus().redo().run()">↻</button>
        </div>
        <EditorContent :editor="editor" class="min-h-[16rem] max-w-none p-3 prose prose-sm dark:prose-invert focus:outline-none" />
    </div>
</template>

<style>
.rte-btn {
    @apply rounded px-2 py-1 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-700;
}
.rte-active {
    @apply bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200;
}
.rich-text-content {
    min-height: 16rem;
}
.rich-text-content:focus {
    outline: none;
}
</style>
