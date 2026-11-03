<template>
  <div class="diy-page">
    <!-- ===================== 顶部操作栏 ===================== -->
    <div class="diy-header">
      <div class="diy-header-left">
        <h1 class="diy-title"><i class="fa fa-paint-brush mr-2"></i>DIY纪念册</h1>
        <input
          v-model="albumTitle"
          type="text"
          class="form-control diy-title-input"
          placeholder="给纪念册起个名字（选填）"
          maxlength="30"
        />
      </div>
      <div class="diy-header-right">
        <button class="btn btn-outline-secondary mr-2" :disabled="saving" @click="clearCanvas">
          <i class="fa fa-trash-o mr-1"></i> 清空画布
        </button>
        <button class="btn btn-primary diy-save-btn" :disabled="saving" @click="handleSave">
          <i :class="saving ? 'fa fa-spinner fa-spin' : 'fa fa-download'" class="mr-1"></i>
          {{ saving ? '生成中...' : '下载到本地' }}
        </button>
      </div>
    </div>

    <div class="diy-workspace">
      <!-- ===================== 左侧工具栏 ===================== -->
      <aside class="diy-panel diy-panel--left">
        <h2 class="diy-panel-title">工具栏</h2>

        <div class="diy-tool-group">
          <!-- 图片：点击选择文件；也可按住拖到画布 -->
          <button type="button" class="diy-tool-btn toolbar-draggable" data-type="image" @click="handleToolClick('image')">
            <i class="fa fa-picture-o"></i>
            <span>上传图片</span>
          </button>
          <input ref="fileInputRef" type="file" accept="image/*" class="d-none" @change="handleFileChange" />

          <!-- 文字：点击在画布中央添加；也可拖到指定位置 -->
          <button type="button" class="diy-tool-btn toolbar-draggable" data-type="text" @click="handleToolClick('text')">
            <i class="fa fa-font"></i>
            <span>添加文字</span>
          </button>
        </div>

        <h3 class="diy-panel-subtitle">贴纸库</h3>
        <p class="diy-hint">点击或拖拽到画布</p>
        <div class="diy-sticker-grid">
          <button
            v-for="sticker in STICKERS"
            :key="sticker"
            type="button"
            class="diy-sticker toolbar-draggable"
            data-type="sticker"
            :data-content="sticker"
            @click="handleToolClick('sticker', sticker)"
          >
            {{ sticker }}
          </button>
        </div>
      </aside>

      <!-- ===================== 中间画布 ===================== -->
      <main ref="stageWrapRef" class="diy-stage-wrap">
        <div class="diy-stage-bar">
          <span class="diy-stage-tip">
            <i class="fa fa-info-circle mr-1"></i>
            拖动元素可移动位置，拖动四角可缩放，拖动顶部圆点可旋转
          </span>
          <span class="diy-stage-size">{{ CANVAS_W }} × {{ CANVAS_H }}</span>
        </div>

        <!-- html2canvas 捕获的目标节点 -->
        <div
          ref="canvasRef"
          class="diy-canvas"
          :style="canvasStyle"
          @mousedown.self="selectedId = null"
        >
          <div
            v-for="el in sortedElements"
            :key="el.id"
            :data-id="el.id"
            class="canvas-element"
            :class="{ 'is-selected': el.id === selectedId }"
            :style="elementStyle(el)"
            @mousedown="selectElement(el.id)"
          >
            <!-- 图片元素 -->
            <img v-if="el.type === 'image'" :src="el.content" class="el-image" alt="" draggable="false" />

            <!-- 文字元素：双击进入编辑 -->
            <input
              v-else-if="el.type === 'text' && editingId === el.id"
              v-model="el.content"
              class="el-text-input"
              :style="{ fontSize: fontSize(el) }"
              @blur="stopEdit"
              @keyup.enter="stopEdit"
              @keyup.esc="stopEdit"
            />
            <div
              v-else-if="el.type === 'text'"
              class="el-text"
              :style="{ fontSize: fontSize(el) }"
              @dblclick.stop="startEdit(el)"
            >
              {{ el.content }}
            </div>

            <!-- 贴纸元素 -->
            <div v-else class="el-sticker" :style="{ fontSize: fontSize(el) }">{{ el.content }}</div>

            <!-- 选中态的视觉控制点（导出时会被隐藏） -->
            <template v-if="el.id === selectedId && !exporting">
              <span class="el-handle el-handle--tl"></span>
              <span class="el-handle el-handle--tr"></span>
              <span class="el-handle el-handle--bl"></span>
              <span class="el-handle el-handle--br"></span>
              <span
                class="el-rotate-handle"
                title="拖动旋转"
                @pointerdown.stop.prevent="startRotate($event, el)"
              >
                <i class="fa fa-rotate-right"></i>
              </span>
            </template>
          </div>
        </div>
      </main>

      <!-- ===================== 右侧属性面板 ===================== -->
      <aside class="diy-panel diy-panel--right">
        <h2 class="diy-panel-title">属性面板</h2>

        <div v-if="!selectedElement" class="diy-empty">
          <i class="fa fa-mouse-pointer"></i>
          <p>点击画布中的元素<br />即可调整它的属性</p>
        </div>

        <template v-else>
          <div class="diy-field">
            <label>元素类型</label>
            <span class="diy-badge">{{ typeLabel(selectedElement.type) }}</span>
          </div>

          <div class="diy-field">
            <label>宽度（px）</label>
            <input
              type="number"
              class="form-control form-control-sm"
              :min="MIN_SIZE"
              :max="CANVAS_W"
              :value="Math.round(selectedElement.width)"
              @change="setSize('width', $event)"
            />
          </div>

          <div class="diy-field">
            <label>高度（px）</label>
            <input
              type="number"
              class="form-control form-control-sm"
              :min="MIN_SIZE"
              :max="CANVAS_H"
              :value="Math.round(selectedElement.height)"
              @change="setSize('height', $event)"
            />
          </div>

          <div class="diy-field">
            <label>旋转 <em>{{ Math.round(selectedElement.rotation) }}°</em></label>
            <input
              type="range"
              class="diy-range"
              min="0"
              max="360"
              step="1"
              :value="Math.round(selectedElement.rotation)"
              @input="setRotation($event)"
            />
          </div>

          <div class="diy-field">
            <label>透明度 <em>{{ Math.round(selectedElement.opacity * 100) }}%</em></label>
            <input
              type="range"
              class="diy-range"
              min="10"
              max="100"
              step="1"
              :value="Math.round(selectedElement.opacity * 100)"
              @input="setOpacity($event)"
            />
          </div>

          <div class="diy-field">
            <label>层级</label>
            <div class="diy-layer-btns">
              <button type="button" title="置顶" @click="reorder(selectedId, 'top')"><i class="fa fa-angle-double-up"></i></button>
              <button type="button" title="上移一层" @click="reorder(selectedId, 'up')"><i class="fa fa-angle-up"></i></button>
              <button type="button" title="下移一层" @click="reorder(selectedId, 'down')"><i class="fa fa-angle-down"></i></button>
              <button type="button" title="置底" @click="reorder(selectedId, 'bottom')"><i class="fa fa-angle-double-down"></i></button>
            </div>
          </div>

          <p v-if="selectedElement.type === 'text'" class="diy-hint">在画布中双击文字可直接修改内容</p>

          <button type="button" class="btn btn-outline-danger btn-block btn-sm mt-3" @click="removeElement(selectedId)">
            <i class="fa fa-trash-o mr-1"></i> 删除该元素
          </button>
        </template>
      </aside>
    </div>

    <!-- 从工具栏拖向画布时的跟随预览 -->
    <div
      v-if="dragPreview"
      class="diy-drag-preview"
      :style="{ left: dragPreview.x + 'px', top: dragPreview.y + 'px', fontSize: dragPreview.type === 'image' ? '24px' : '40px' }"
    >
      <i v-if="dragPreview.type === 'image'" class="fa fa-picture-o"></i>
      <span v-else>{{ dragPreview.content || '文字' }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import interact from 'interactjs'
import html2canvas from 'html2canvas'

/* ============================================================
 * 常量
 * ============================================================ */
// 画布逻辑尺寸（生成图片时按这个尺寸渲染，与屏幕显示缩放无关）
const CANVAS_W = 800
const CANVAS_H = 600
const EXPORT_SCALE = 2 // 导出倍率：1600 × 1200
const MIN_SIZE = 24 // 元素最小宽高（逻辑像素）

// 基础贴纸库
const STICKERS = [
  '😀', '🥳', '😍', '🌟', '🎉', '💖', '🌸', '🍀',
  '🎈', '⭐', '🌈', '🎀', '🍰', '☕', '🎵', '📚',
  '🐱', '🐶', '🌻', '🍉', '🧋', '🏀', '🎓', '📷'
]

/* ============================================================
 * 画布状态
 * ============================================================ */
const albumTitle = ref('')
const canvasElements = ref([]) // 元素数据模型数组
const selectedId = ref(null)
const editingId = ref(null)
const zoom = ref(1) // 画布显示缩放比（响应式）
const exporting = ref(false) // 生成图片中（此时隐藏选中框）
const saving = ref(false)

const canvasRef = ref(null)
const stageWrapRef = ref(null)
const fileInputRef = ref(null)

/* ============================================================
 * 计算属性
 * ============================================================ */
// 按 zIndex 升序渲染，保证层级正确
const sortedElements = computed(() =>
  [...canvasElements.value].sort((a, b) => a.zIndex - b.zIndex)
)

const selectedElement = computed(() =>
  canvasElements.value.find((el) => el.id === selectedId.value) || null
)

const canvasStyle = computed(() => ({
  width: CANVAS_W * zoom.value + 'px',
  height: CANVAS_H * zoom.value + 'px'
}))

/* ============================================================
 * 工具函数
 * ============================================================ */
const clamp = (value, min, max) => Math.min(Math.max(value, min), max)

let idSeed = 0
const nextId = () => `el_${Date.now().toString(36)}_${(idSeed++).toString(36)}`
const nextZ = () => canvasElements.value.reduce((max, el) => Math.max(max, el.zIndex), 0) + 1
const findElement = (id) => canvasElements.value.find((el) => el.id === id)

const typeLabel = (type) => ({ image: '图片', text: '文字', sticker: '贴纸' }[type] || type)

// 文字/贴纸的字号由元素高度推导，保证与"高度"属性联动
const fontSize = (el) => `${Math.max(8, el.height * zoom.value * 0.62)}px`

// 元素的内联样式：所有尺寸都乘以 zoom，实现响应式且不依赖 transform 缩放
const elementStyle = (el) => ({
  left: el.x * zoom.value + 'px',
  top: el.y * zoom.value + 'px',
  width: el.width * zoom.value + 'px',
  height: el.height * zoom.value + 'px',
  transform: `rotate(${el.rotation}deg)`,
  opacity: el.opacity,
  zIndex: el.zIndex
})

/* ============================================================
 * 元素的增删改
 * ============================================================ */
/**
 * 添加元素
 * @param {'image'|'text'|'sticker'} type
 * @param {string} content 图片为 dataURL，文字为文案，贴纸为 emoji
 * @param {Object} extra   额外尺寸覆盖（如按图片原始比例计算后的宽高）
 * @param {Object} point   指定放置点（画布逻辑坐标，元素以其为中心）
 */
const addElement = (type, content = '', extra = {}, point = null) => {
  const defaults = {
    image: { width: 240, height: 180, content },
    text: { width: 220, height: 60, content: content || '双击编辑文字' },
    sticker: { width: 96, height: 96, content }
  }[type]

  if (!defaults) return

  const el = {
    id: nextId(),
    type,
    content: defaults.content,
    x: 0,
    y: 0,
    width: extra.width || defaults.width,
    height: extra.height || defaults.height,
    rotation: 0,
    opacity: 1,
    zIndex: nextZ()
  }

  // 默认放在画布中央，或放在指定落点
  const centerX = point ? point.x : CANVAS_W / 2
  const centerY = point ? point.y : CANVAS_H / 2
  el.x = clamp(centerX - el.width / 2, 0, Math.max(0, CANVAS_W - el.width))
  el.y = clamp(centerY - el.height / 2, 0, Math.max(0, CANVAS_H - el.height))

  canvasElements.value.push(el)
  selectedId.value = el.id
  nextTick(() => bindElementInteraction(el))
  return el
}

const removeElement = (id) => {
  const index = canvasElements.value.findIndex((el) => el.id === id)
  if (index === -1) return
  const node = getElementNode(id)
  if (node) interact(node).unset() // 解绑交互，避免内存泄漏
  boundIds.delete(id)
  canvasElements.value.splice(index, 1)
  if (selectedId.value === id) selectedId.value = null
  if (editingId.value === id) editingId.value = null
}

const clearCanvas = () => {
  if (!canvasElements.value.length) return
  if (!window.confirm('确定要清空画布上的所有内容吗？')) return
  canvasElements.value.forEach((el) => {
    const node = getElementNode(el.id)
    if (node) interact(node).unset()
  })
  boundIds.clear()
  canvasElements.value = []
  selectedId.value = null
  editingId.value = null
}

const selectElement = (id) => {
  selectedId.value = id
  if (editingId.value && editingId.value !== id) editingId.value = null
}

const startEdit = (el) => {
  editingId.value = el.id
}

const stopEdit = () => {
  editingId.value = null
}

/* ============================================================
 * 属性面板操作
 * ============================================================ */
const setSize = (key, event) => {
  const el = selectedElement.value
  if (!el) return
  const limit = key === 'width' ? CANVAS_W : CANVAS_H
  const value = clamp(Number(event.target.value) || MIN_SIZE, MIN_SIZE, limit)
  el[key] = value
  clampIntoCanvas(el)
}

const setRotation = (event) => {
  const el = selectedElement.value
  if (!el) return
  el.rotation = Number(event.target.value) || 0
}

const setOpacity = (event) => {
  const el = selectedElement.value
  if (!el) return
  el.opacity = (Number(event.target.value) || 100) / 100
}

// 层级调整：按 zIndex 排序后移动位置，再统一重新编号（1..n）
const reorder = (id, mode) => {
  const list = [...canvasElements.value].sort((a, b) => a.zIndex - b.zIndex)
  const index = list.findIndex((el) => el.id === id)
  if (index === -1) return

  const [item] = list.splice(index, 1)
  let target
  if (mode === 'up') target = Math.min(list.length, index + 1)
  else if (mode === 'down') target = Math.max(0, index - 1)
  else if (mode === 'top') target = list.length
  else target = 0

  list.splice(target, 0, item)
  list.forEach((el, i) => { el.zIndex = i + 1 })
}

/* ============================================================
 * 图片上传
 * ============================================================ */
// 工具栏点击：若刚刚发生过"拖拽到画布"，则忽略这次 click，避免重复添加
const handleToolClick = (type, content = '') => {
  if (suppressClick) return
  if (type === 'image') {
    fileInputRef.value?.click()
    return
  }
  addElement(type, content)
}

const handleFileChange = (event) => {
  const file = event.target.files && event.target.files[0]
  event.target.value = '' // 允许连续选择同一张图片
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('请选择图片文件')
    return
  }

  const reader = new FileReader()
  reader.onload = () => {
    const src = reader.result
    // 读取原始宽高，按比例缩放到合适尺寸后再放进画布
    const image = new Image()
    image.onload = () => {
      const maxSide = 280
      const ratio = Math.min(1, maxSide / Math.max(image.width, image.height))
      addElement('image', src, {
        width: Math.round(image.width * ratio) || 240,
        height: Math.round(image.height * ratio) || 180
      })
    }
    image.onerror = () => addElement('image', src)
    image.src = src
  }
  reader.readAsDataURL(file)
}

/* ============================================================
 * interact.js —— 拖拽 / 缩放
 * ============================================================ */
const boundIds = new Set() // 已绑定过 interact 的元素 id
let suppressClick = false // 工具栏"拖拽到画布"后，抑制紧随其后的 click

const getElementNode = (id) =>
  canvasRef.value ? canvasRef.value.querySelector(`.canvas-element[data-id="${id}"]`) : null

const getClientPoint = (event) => {
  // interact 事件在不同版本下用 clientX 或 pageX 暴露指针位置
  if (typeof event.clientX === 'number') return { x: event.clientX, y: event.clientY }
  return { x: event.pageX - window.scrollX, y: event.pageY - window.scrollY }
}

// 屏幕坐标 → 画布逻辑坐标
const clientToCanvas = (clientX, clientY) => {
  const rect = canvasRef.value.getBoundingClientRect()
  return {
    x: (clientX - rect.left) / zoom.value,
    y: (clientY - rect.top) / zoom.value
  }
}

// 边界检测：元素不得拖出画布
const clampIntoCanvas = (el) => {
  el.x = clamp(el.x, 0, Math.max(0, CANVAS_W - el.width))
  el.y = clamp(el.y, 0, Math.max(0, CANVAS_H - el.height))
}

const moveElement = (id, dx, dy) => {
  const el = findElement(id)
  if (!el) return
  el.x += dx
  el.y += dy
  clampIntoCanvas(el)
}

/**
 * 缩放元素（支持任意旋转角度）
 * 关键点：interact 给出的 dx/dy 是屏幕坐标增量，需要反向旋转 θ 换算到元素本地坐标；
 * 再按"对边固定"的几何关系补偿 x/y，保证缩放时视觉上锚点不动。
 */
const resizeElement = (id, dxScreen, dyScreen, edges) => {
  const el = findElement(id)
  if (!el) return

  const rad = (el.rotation * Math.PI) / 180
  const cos = Math.cos(rad)
  const sin = Math.sin(rad)

  // 屏幕增量 → 元素本地增量
  const dl = dxScreen * cos + dyScreen * sin
  const dt = -dxScreen * sin + dyScreen * cos

  let dw = 0
  let dh = 0
  if (edges.right) dw += dl
  if (edges.left) dw -= dl
  if (edges.bottom) dh += dt
  if (edges.top) dh -= dt

  const oldW = el.width
  const oldH = el.height
  const newW = clamp(oldW + dw, MIN_SIZE, CANVAS_W)
  const newH = clamp(oldH + dh, MIN_SIZE, CANVAS_H)
  const dW = newW - oldW
  const dH = newH - oldH
  if (dW === 0 && dH === 0) return

  let x = el.x
  let y = el.y
  if (edges.right) { x += -(dW / 2) * (1 - cos); y += (dW / 2) * sin }
  if (edges.left) { x += -(dW / 2) * (1 + cos); y += -(dW / 2) * sin }
  if (edges.top) { x += (dH / 2) * sin; y += -(dH / 2) * (1 + cos) }
  if (edges.bottom) { x += -(dH / 2) * sin; y += -(dH / 2) * (1 - cos) }

  el.width = newW
  el.height = newH
  el.x = x
  el.y = y
  clampIntoCanvas(el)
}

// 为画布中的元素绑定拖拽 + 缩放（逐个绑定，元素增删时同步）
const bindElementInteraction = (el) => {
  if (boundIds.has(el.id)) return
  const node = getElementNode(el.id)
  if (!node) return
  boundIds.add(el.id)

  interact(node)
    .draggable({
      inertia: false,
      // 旋转手柄与文字输入框不参与拖拽
      ignoreFrom: '.el-rotate-handle, .el-text-input',
      listeners: {
        start: () => selectElement(el.id),
        move: (event) => moveElement(el.id, event.dx / zoom.value, event.dy / zoom.value)
      }
    })
    .resizable({
      edges: { left: true, right: true, bottom: true, top: true },
      margin: 8,
      listeners: {
        start: () => selectElement(el.id),
        move: (event) => resizeElement(el.id, event.dx / zoom.value, event.dy / zoom.value, event.edges)
      }
    })
}

/* ============================================================
 * 旋转（自绘手柄 + 原生指针事件，避免与拖拽冲突）
 * ============================================================ */
const rotatingId = ref(null)

const startRotate = (event, el) => {
  rotatingId.value = el.id
  selectElement(el.id)
  window.addEventListener('pointermove', onRotateMove)
  window.addEventListener('pointerup', stopRotate)
  window.addEventListener('pointercancel', stopRotate)
  onRotateMove(event)
}

const onRotateMove = (event) => {
  const id = rotatingId.value
  if (!id) return
  const el = findElement(id)
  const node = getElementNode(id)
  if (!el || !node) return

  // 以元素中心为原点，计算指针所在角度
  const rect = node.getBoundingClientRect()
  const centerX = rect.left + rect.width / 2
  const centerY = rect.top + rect.height / 2
  const angle = (Math.atan2(event.clientY - centerY, event.clientX - centerX) * 180) / Math.PI + 90
  el.rotation = Math.round(((angle % 360) + 360) % 360)
}

const stopRotate = () => {
  rotatingId.value = null
  window.removeEventListener('pointermove', onRotateMove)
  window.removeEventListener('pointerup', stopRotate)
  window.removeEventListener('pointercancel', stopRotate)
}

/* ============================================================
 * 工具栏拖拽到画布
 * ============================================================ */
const dragPreview = ref(null) // { x, y, type, content } 跟随鼠标的预览
let dragMoved = 0

const bindToolbarInteraction = () => {
  interact('.toolbar-draggable').draggable({
    inertia: false,
    listeners: {
      start: (event) => {
        const target = event.target
        const point = getClientPoint(event)
        dragMoved = 0
        dragPreview.value = {
          x: point.x,
          y: point.y,
          type: target.dataset.type,
          content: target.dataset.content || ''
        }
      },
      move: (event) => {
        if (!dragPreview.value) return
        dragMoved += Math.abs(event.dx) + Math.abs(event.dy)
        const point = getClientPoint(event)
        dragPreview.value.x = point.x
        dragPreview.value.y = point.y
      },
      end: (event) => {
        const preview = dragPreview.value
        dragPreview.value = null
        if (!preview || dragMoved < 6) return // 视为点击，交给 click 处理

        const point = getClientPoint(event)
        if (point.x < 0 || point.y < 0) return

        const rect = canvasRef.value.getBoundingClientRect()
        const insideCanvas =
          point.x >= rect.left && point.x <= rect.right &&
          point.y >= rect.top && point.y <= rect.bottom

        // 拖拽结束后会紧跟着触发一次 click，需要抑制掉，避免重复添加
        suppressClick = true
        setTimeout(() => { suppressClick = false }, 300)

        if (!insideCanvas) return
        addElement(preview.type, preview.content, {}, clientToCanvas(point.x, point.y))
      }
    }
  })
}

/* ============================================================
 * 响应式缩放
 * ============================================================ */
let resizeObserver = null

const updateZoom = () => {
  const wrap = stageWrapRef.value
  if (!wrap) return
  const available = wrap.clientWidth - 8
  const next = clamp(available / CANVAS_W, 0.2, 1)
  zoom.value = Number(next.toFixed(4))
}

/* ============================================================
 * 生成图片 & 保存
 * ============================================================ */
// 用 html2canvas 把画布节点渲染成图片（按固定逻辑尺寸 800×600 输出）
const generateImage = async () => {
  const node = canvasRef.value
  if (!node) throw new Error('画布不存在')

  const prevSelected = selectedId.value
  const prevEditing = editingId.value
  selectedId.value = null
  editingId.value = null
  exporting.value = true
  await nextTick()
  // 等一帧，确保选中框已从 DOM 中移除后再截图
  await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)))

  try {
    const canvas = await html2canvas(node, {
      // 显示尺寸是 800 * zoom，导出固定为 800 * EXPORT_SCALE
      scale: EXPORT_SCALE / zoom.value,
      backgroundColor: '#ffffff',
      useCORS: true,
      logging: false,
      width: Math.round(CANVAS_W * zoom.value),
      height: Math.round(CANVAS_H * zoom.value)
    })
    return canvas
  } finally {
    exporting.value = false
    selectedId.value = prevSelected
    editingId.value = prevEditing
  }
}

// 把生成的图片通过浏览器「另存为」下载到本地
const downloadImage = (canvas) => {
  const safeTitle = albumTitle.value.trim().replace(/[\\/:*?"<>|]/g, '') || '我的纪念册'
  canvas.toBlob((blob) => {
    if (!blob) {
      alert('生成图片失败，请重试')
      return
    }
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `${safeTitle}.png`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    // 延迟释放，避免部分浏览器下载未开始就被回收
    setTimeout(() => URL.revokeObjectURL(url), 1000)
  }, 'image/png')
}

const handleSave = async () => {
  if (!canvasElements.value.length) {
    alert('画布还是空的，先添加一些内容吧')
    return
  }

  saving.value = true
  try {
    const canvas = await generateImage()
    downloadImage(canvas)
  } catch (error) {
    console.error('生成纪念册图片失败:', error)
    alert('生成图片失败，请重试')
  } finally {
    saving.value = false
  }
}

/* ============================================================
 * 生命周期
 * ============================================================ */
onMounted(() => {
  bindToolbarInteraction()
  updateZoom()

  if (stageWrapRef.value && typeof ResizeObserver !== 'undefined') {
    resizeObserver = new ResizeObserver(updateZoom)
    resizeObserver.observe(stageWrapRef.value)
  } else {
    window.addEventListener('resize', updateZoom)
  }
})

onBeforeUnmount(() => {
  stopRotate()
  if (resizeObserver) {
    resizeObserver.disconnect()
    resizeObserver = null
  } else {
    window.removeEventListener('resize', updateZoom)
  }
  canvasElements.value.forEach((el) => {
    const node = getElementNode(el.id)
    if (node) interact(node).unset()
  })
  boundIds.clear()
  interact('.toolbar-draggable').unset()
})

// 双击进入编辑后自动聚焦输入框
watch(editingId, async (id) => {
  if (!id) return
  await nextTick()
  const input = canvasRef.value?.querySelector('.el-text-input')
  if (input) {
    input.focus()
    input.select()
  }
})
</script>

<style scoped>
.diy-page {
  min-height: 100vh;
  padding: 20px 16px 40px;
  background: #f7f8fa;
}

/* ---------- 顶部操作栏 ---------- */
.diy-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 16px;
}

.diy-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1 1 320px;
}

.diy-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
  white-space: nowrap;
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);
}

.diy-title-input {
  max-width: 280px;
  border-radius: 20px;
}

.diy-save-btn {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  border: none;
  border-radius: 20px;
  font-weight: 600;
}

.diy-save-btn:disabled {
  opacity: 0.7;
}

/* ---------- 工作区三栏布局 ---------- */
.diy-workspace {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.diy-panel {
  flex: 0 0 auto;
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.diy-panel--left {
  width: 200px;
}

.diy-panel--right {
  width: 250px;
}

.diy-panel-title {
  margin: 0 0 12px;
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-primary, #FF8A5C);
}

.diy-panel-subtitle {
  margin: 18px 0 4px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #333;
}

/* ---------- 左侧工具栏 ---------- */
.diy-tool-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.diy-tool-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 10px 12px;
  border: 1px dashed #ffc7b0;
  border-radius: 10px;
  background: #fff8f5;
  color: #444;
  font-size: 0.92rem;
  cursor: grab;
  touch-action: none;
  transition: all 0.2s ease;
}

.diy-tool-btn i {
  color: var(--color-primary, #FF8A5C);
  font-size: 1.1rem;
}

.diy-tool-btn:hover {
  border-color: var(--color-primary, #FF8A5C);
  background: #fff1ea;
}

.diy-sticker-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}

.diy-sticker {
  padding: 6px 0;
  font-size: 1.3rem;
  line-height: 1;
  background: #f7f8fa;
  border: 1px solid transparent;
  border-radius: 8px;
  cursor: grab;
  touch-action: none;
  transition: all 0.2s ease;
}

.diy-sticker:hover {
  background: #fff1ea;
  border-color: #ffc7b0;
}

.diy-hint {
  margin: 0 0 8px;
  font-size: 0.75rem;
  color: #999;
  line-height: 1.5;
}

/* ---------- 中间画布 ---------- */
.diy-stage-wrap {
  flex: 1 1 auto;
  min-width: 0;
}

.diy-stage-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 0.78rem;
  color: #8a8a8a;
}

.diy-canvas {
  position: relative;
  overflow: hidden;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
  user-select: none;
}

.canvas-element {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  transform-origin: center center;
  cursor: move;
  touch-action: none;
}

.canvas-element.is-selected {
  outline: 2px solid var(--color-primary, #FF8A5C);
  outline-offset: 0;
}

.el-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  pointer-events: none;
}

.el-text {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  font-weight: 600;
  color: #2f2f2f;
  line-height: 1.15;
  white-space: pre-wrap;
  word-break: break-word;
  overflow: hidden;
}

.el-text-input {
  width: 100%;
  height: 100%;
  padding: 0 4px;
  text-align: center;
  font-weight: 600;
  color: #2f2f2f;
  border: 1px dashed var(--color-primary, #FF8A5C);
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.9);
  outline: none;
}

.el-sticker {
  line-height: 1;
}

/* 四角缩放点（视觉提示，交互由 interact.js 的边距检测实现） */
.el-handle {
  position: absolute;
  width: 10px;
  height: 10px;
  background: #fff;
  border: 2px solid var(--color-primary, #FF8A5C);
  border-radius: 50%;
  pointer-events: none;
}

.el-handle--tl { top: -5px; left: -5px; }
.el-handle--tr { top: -5px; right: -5px; }
.el-handle--bl { bottom: -5px; left: -5px; }
.el-handle--br { bottom: -5px; right: -5px; }

/* 顶部旋转手柄 */
.el-rotate-handle {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-primary, #FF8A5C);
  color: #fff;
  border-radius: 50%;
  font-size: 0.7rem;
  cursor: grab;
  touch-action: none;
}

/* ---------- 右侧属性面板 ---------- */
.diy-empty {
  padding: 24px 8px;
  text-align: center;
  color: #aaa;
}

.diy-empty i {
  font-size: 2rem;
  margin-bottom: 10px;
  display: block;
}

.diy-empty p {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.6;
}

.diy-field {
  margin-bottom: 14px;
}

.diy-field > label {
  display: flex;
  justify-content: space-between;
  margin-bottom: 6px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #555;
}

.diy-field > label em {
  font-style: normal;
  font-weight: 400;
  color: #999;
}

.diy-range {
  width: 100%;
  accent-color: #FF6B9D;
}

.diy-badge {
  display: inline-block;
  padding: 2px 10px;
  font-size: 0.78rem;
  border-radius: 10px;
  background: #fff1ea;
  color: var(--color-primary, #FF8A5C);
}

.diy-layer-btns {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}

.diy-layer-btns button {
  padding: 6px 0;
  background: #f7f8fa;
  border: 1px solid #eee;
  border-radius: 8px;
  color: #666;
  cursor: pointer;
  transition: all 0.2s ease;
}

.diy-layer-btns button:hover {
  background: #fff1ea;
  border-color: #ffc7b0;
  color: var(--color-primary, #FF8A5C);
}

/* ---------- 拖拽预览 ---------- */
.diy-drag-preview {
  position: fixed;
  z-index: 2000;
  transform: translate(-50%, -50%);
  pointer-events: none;
  opacity: 0.85;
  color: var(--color-primary, #FF8A5C);
}

/* ---------- 移动端/窄屏适配 ---------- */
@media (max-width: 991.98px) {
  .diy-workspace {
    flex-wrap: wrap;
  }

  .diy-panel--left,
  .diy-panel--right {
    width: 100%;
  }

  .diy-sticker-grid {
    grid-template-columns: repeat(8, 1fr);
  }

  .diy-stage-wrap {
    order: -1;
    width: 100%;
  }
}
</style>
